<?php

namespace common\modules\abiturient\models;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerBadGetReferenceRequest;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\soapException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\dictionary\Country;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\Gender;
use common\models\errors\RecordNotValid;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\comparison\interfaces\IHaveVirtualPropsToCompare;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\UserProfile;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\chat\AbiturientChatUser;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\interfaces\IQuestionnaireValidateModelInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use common\modules\abiturient\models\validators\SnilsValidator;
use common\modules\abiturient\validators\extenders\PersonalData\PersonalDataAppsCheckValidation;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


































class PersonalData extends ChangeHistoryDecoratedModel implements
    QuestionaryConnectedInterface,
    ChangeLoggedModelInterface,
    IQuestionnaireValidateModelInterface,
    IHaveVirtualPropsToCompare,
    ICanGivePropsToCompare,
    IHaveIdentityProp
{
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    protected static ?string $GENDER_MALE = null;
    protected static ?string $GENDER_FEMALE = null;

    const SCENARIO_REGISTRATION = 'abit_registration';
    const SCENARIO_GET_ANKETA = 'get_anketa';
    const SCENARIO_DEPERSONALIZATION = 'depersonalization';

    public $validation_extender;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->validation_extender = new PersonalDataAppsCheckValidation([
            'model' => $this
        ]);
    }

    public static function getGenderMale(): ?string
    {
        if (static::$GENDER_MALE === null) {
            static::$GENDER_MALE = Yii::$app->configurationManager->getCode('male_guid');
        }
        return static::$GENDER_MALE;
    }

    public static function getGenderFemale(): ?string
    {
        if (static::$GENDER_FEMALE === null) {
            static::$GENDER_FEMALE = Yii::$app->configurationManager->getCode('female_guid');
        }
        return static::$GENDER_FEMALE;
    }

    public static function tableName()
    {
        return '{{%personal_data}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        $base_rules = [
            [
                [
                    'firstname',
                    'middlename',
                    'lastname',
                    'birth_place',
                ],
                'trim'
            ],
            [
                [
                    'gender_id',
                    'country_id',
                    'language_id',
                    'questionary_id',
                ],
                'integer'
            ],
            [
                [
                    'need_dormitory',
                    'need_pc_course',
                    'need_po_course',
                    'need_engineer_class',
                ],
                'boolean'
            ],
            [
                ['gender'],
                'in',
                'range' => static::availableGenderCodes()
            ],
            [
                [
                    'gender',
                    'firstname',
                    'middlename',
                    'lastname',
                    'birthdate',
                    'main_phone',
                    'secondary_phone',
                    'language_code',
                    'birth_place',
                    'entrant_unique_code',
                    'entrant_unique_code_special_quota',
                ],
                'string',
                'max' => 255
            ],
            [
                [
                    'passport_series',
                    'passport_number',
                    'snils',
                ],
                'string',
                'max' => 50
            ],
            [
                ['snils'],
                'string',
                'max' => 14
            ],
            [
                'snils',
                SnilsValidator::class
            ],
            [
                [
                    'firstname',
                    'lastname',
                    'birthdate',
                ],
                'required',
                'on' => self::SCENARIO_REGISTRATION
            ],
            [
                [
                    'firstname',
                    'lastname',
                    'birthdate',
                    'main_phone',
                    'gender_id',
                ],
                'required',
                'except' => [
                    self::SCENARIO_GET_ANKETA,
                    self::SCENARIO_DEPERSONALIZATION
                ]
            ],
            [
                [
                    'firstname',
                    'lastname',
                    'middlename',
                    'passport_series',
                    'passport_number',
                ],
                'required',
                'on' => self::SCENARIO_DEPERSONALIZATION
            ],
            [
                [
                    'need_dormitory',
                    'need_pc_course',
                    'need_po_course',
                    'need_engineer_class',
                ],
                'default',
                'value' => false
            ],
            [
                'main_phone',
                'checkPhone',
            ],
        ];
        return ArrayHelper::merge($base_rules, $this->validation_extender ? $this->validation_extender->getRules() : []);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTRATION] = ['firstname', 'lastname', 'birthdate', 'passport_number'];
        $scenarios[self::SCENARIO_GET_ANKETA] = ['firstname', 'lastname', 'birthdate', 'passport_number', 'main_phone', 'gender', 'gender_id', 'snils', 'language_code'];
        return $scenarios;
    }

    public function checkPhone($attribute, $params)
    {
        if (strpos($this->$attribute, '_')) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/questionary/personal-data',
                    'Подсказка с ошибкой для поля "main_phone" формы "Персональные данные": `Заполните номер телефона полностью`'
                )
            );
        }
    }

    public function getRelGender()
    {
        return $this->hasOne(Gender::class, ['id' => 'gender_id']);
    }

    public function getLanguage()
    {
        return $this->hasOne(ForeignLanguage::class, ['id' => 'language_id']);
    }

    public function getLanguageCode()
    {
        if ($this->language !== null) {
            return $this->language->code;
        }
        return null;
    }

    public static function availableGenderCodes(): array
    {
        return Gender::find()
            ->select('code')
            ->andWhere(['archive' => false])
            ->andWhere(['ref_key' => [
                Yii::$app->configurationManager->getCode('male_guid'),
                Yii::$app->configurationManager->getCode('female_guid')
            ]])
            ->column();
    }

    public function getGenderCode()
    {
        if ($this->relGender !== null) {
            return $this->relGender->code;
        }
        return null;
    }

    public function getCitizenship()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

    public function isRussianCitizenship(): bool
    {
        return ArrayHelper::getValue($this, 'citizenship.ref_key') == Yii::$app->configurationManager->getCode('russia_guid');
    }

    


    public function attributeLabels()
    {
        return [
            'snils' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "snils" формы "Персональные данные": `СНИЛС`'),
            'gender' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "gender" формы "Персональные данные": `Пол`'),
            'lastname' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "lastname" формы "Персональные данные": `Фамилия`'),
            'firstname' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "firstname" формы "Персональные данные": `Имя`'),
            'gender_id' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "gender_id" формы "Персональные данные": `Пол`'),
            'birthdate' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "birthdate" формы "Персональные данные": `Дата рождения`'),
            'country_id' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "country_id" формы "Персональные данные": `Гражданство`'),
            'citizenshipName' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "country_id" формы "Персональные данные": `Гражданство`'),
            'created_at' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "created_at" формы "Персональные данные": `Создано`'),
            'genderName' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "genderName" формы "Персональные данные": `Пол`'),
            'main_phone' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "main_phone" формы "Персональные данные": `Основной номер телефона`'),
            'middlename' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "middlename" формы "Персональные данные": `Отчество`'),
            'updated_at' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "updated_at" формы "Персональные данные": `Обновлено`'),
            'absFullName' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "absFullName" формы "Персональные данные": `ФИО`'),
            'birth_place' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "birth_place" формы "Персональные данные": `Место рождения`'),
            'language_id' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "language_id" формы "Персональные данные": `Изучаемый иностранный язык`'),
            'language_code' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "language_code" формы "Персональные данные": `Изучаемый иностранный язык`'),
            'need_dormitory' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "need_dormitory" формы "Персональные данные": `Нуждаемость в общежитии`'),
            'questionary_id' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "questionary_id" формы "Персональные данные": `Анкета`'),
            'passport_number' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "passport_number" формы "Персональные данные": `Номер паспорта`'),
            'passport_series' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "passport_series" формы "Персональные данные": `Серия паспорта`'),
            'secondary_phone' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "secondary_phone" формы "Персональные данные": `Дополнительный номер телефона`'),
            'preparedMainPhone' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "preparedMainPhone" формы "Персональные данные": `Основной номер телефона`'),
            'entrant_unique_code' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "entrant_unique_code" формы "Персональные данные": `Уникальный код, присвоенный поступающему`'),
            'entrant_unique_code_special_quota' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "entrant_unique_code_special_quota" формы "Персональные данные": `Уникальный код для специальной квоты`'),
            'foreignLanguageName' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "foreignLanguageName" формы "Персональные данные": `Изучаемый иностранный язык`'),
            'humanizedNeedDormitory' => Yii::t('abiturient/questionary/personal-data', 'Подпись для поля "humanizedNeedDormitory" формы "Персональные данные": `Нуждаемость в общежитии`'),
        ];
    }

    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->abiturientQuestionary && $this->abiturientQuestionary->draft_status == IDraftable::DRAFT_STATUS_CREATED) {
            $userProfile = ArrayHelper::getValue($this, 'abiturientQuestionary.user.userProfile');
            if ($userProfile) {
                

                $userProfile->firstname = $this->firstname;
                $userProfile->middlename = $this->middlename;
                $userProfile->lastname = $this->lastname;
                $userProfile->gender = $this->gender;
                $userProfile->birthday = $this->birthdate;
                $userProfile->passport_series = $this->passport_series;
                $userProfile->passport_number = $this->passport_number;
                $userProfile->country_id = $this->country_id;

                if (!$userProfile->save()) {
                    throw new RecordNotValid($userProfile);
                }

                AbiturientChatUser::updateUserAccount($userProfile->user_id, $userProfile->getFullName());
            }
        }
    }

    protected function prepareAttributes()
    {
        if ($this->passport_series != null) {
            $this->passport_series = str_replace(' ', '', $this->passport_series);
        }
        if ($this->passport_number != null) {
            $this->passport_number = str_replace(' ', '', $this->passport_number);
        }

        if ($this->snils != null && preg_match('/___-___-___ __/', $this->snils)) {
            $this->snils = null;
        }
    }

    protected function beforeCheckChangedAttributes()
    {
        $this->prepareAttributes();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->prepareAttributes();

            
            if (empty($this->abiturientQuestionary->user->userRef)) {
                $this->entrant_unique_code = $this->snils;
            }

            return true;
        }
        return false;
    }

    


    public function getEmail()
    {
        return $this->abiturientQuestionary->user->email;
    }

    








    public function getEntrantRef()
    {
        return UserReferenceTypeManager::GetProcessedUserReferenceType($this->abiturientQuestionary->user);
    }

    public function getFullName()
    {
        if ($this->firstname || $this->lastname) {
            return Html::encode(implode(' ', [$this->firstname, $this->lastname]));
        }
        return null;
    }

    public function getFio()
    {
        $substrFirstname = substr($this->firstname, 0, 2);
        $substrMiddlename = substr($this->middlename, 0, 2);
        return Html::encode("{$this->lastname} {$substrFirstname}.{$substrMiddlename}.");
    }

    public function getAbsFullName()
    {
        return Html::encode("{$this->lastname} {$this->firstname} {$this->middlename}");
    }

    public function getFormated_birthdate()
    {
        return date('Y-m-d', strtotime($this->birthdate));
    }

    public function __set($name, $value)
    {
        $value = $this->encodeProp($name, $value);

        if ($name == 'birthdate') {
            $value = (string)date('d.m.Y', strtotime($value));
        }
        parent::__set($name, $value);
    }

    


    public static function getMaxBirthdateFormatted()
    {
        $endDate = date('31.12.Y', strtotime('-' . Yii::$app->configurationManager->getCode('min_age') . ' year'));
        if (Yii::$app->configurationManager->getCode('min_age') == 0) {
            $endDate = '-1d';
        }
        return $endDate;
    }

    


    public static function getMaxBirthdateForValidator()
    {
        $endDate = date('31.12.Y', strtotime('-' . Yii::$app->configurationManager->getCode('min_age') . ' year'));
        if (Yii::$app->configurationManager->getCode('min_age') == 0) {
            $endDate = date('d.m.Y', strtotime('-1 day'));
        }

        return $endDate;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'country_id' => function ($model) {
                return ArrayHelper::getValue($model, 'citizenship.name');
            },
            'main_phone',
            'birth_place',
            'gender_id' => function ($model) {
                return ArrayHelper::getValue($model, 'relGender.description');
            },
            'firstname',
            'middlename',
            'lastname',
            'birthdate',
            'language_id' => function ($model) {
                return ArrayHelper::getValue($model, 'language.description');
            },
            'need_dormitory' => function ($model) {
                return $model->need_dormitory ? Yii::t(
                    'abiturient/questionary/personal-data',
                    'Подпись наличия согласия на необходимость общежития: `Да`'
                ) : Yii::t(
                    'abiturient/questionary/personal-data',
                    'Подпись отсутствия согласия на необходимость общежития: `Нет`'
                );
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_PERSONAL_DATA;
    }

    public function getValidatedName(): string
    {
        return Yii::t(
            'abiturient/questionary/personal-data',
            'Валидационное имя модели: `Персональные данные`'
        );
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function getVirtualProps(): array
    {
        return [
            'preparedMainPhone' => function (PersonalData $model) {
                return str_replace('-', '', $model->main_phone);
            },
            'preparedSecondaryPhone' => function (PersonalData $model) {
                return str_replace('-', '', $model->secondary_phone);
            },
        ];
    }

    public function getGenderName()
    {
        return ArrayHelper::getValue($this, 'relGender.description');
    }

    public function getForeignLanguageName()
    {
        return ArrayHelper::getValue($this, 'language.description');
    }

    public function getCitizenshipName()
    {
        return ArrayHelper::getValue($this, 'citizenship.name');
    }

    public function getHumanizedNeedDormitory()
    {
        return $this->need_dormitory ? 'Да' : 'Нет';
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(array_keys($this->attributes), [
            'genderName',
            'foreignLanguageName',
            'citizenshipName',
            'humanizedNeedDormitory',
            'absFullName',
        ]);
    }

    public function getIdentityString(): string
    {
        return "{$this->absFullName}{$this->genderName}{$this->citizenshipName}";
    }

    


    public function getLanguages(): array
    {
        $tnForeignLanguage = ForeignLanguage::tableName();
        return ArrayHelper::map(
            ForeignLanguage::find()
                ->notMarkedToDelete()
                ->active()
                ->orFilterWhere(["{$tnForeignLanguage}.id" => $this->language_id])
                ->orderBy(["{$tnForeignLanguage}.description" => SORT_ASC])
                ->all(),
            'id',
            'description'
        );
    }

    public function getAge(): int
    {
        return (int)date_diff(date_create($this->birthdate), date_create('now'))->y;
    }
}
