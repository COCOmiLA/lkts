<?php

namespace common\modules\abiturient\models\parentData;

use common\components\ErrorMessageAnalyzer;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\soapException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\dictionary\FamilyType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\EntityForDuplicatesFind;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\comparison\interfaces\IHaveVirtualPropsToCompare;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\models\traits\ArchiveTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;























class ParentData extends ChangeHistoryDecoratedModel implements
    QuestionaryConnectedInterface,
    ArchiveModelInterface,
    ChangeLoggedModelInterface,
    IHasRelations,
    IHaveIdentityProp,
    IHaveVirtualPropsToCompare,
    ICanBeStringified
{
    use ArchiveTrait;
    use HtmlPropsEncoder;

    


    public static function tableName()
    {
        return '{{%parent_data}}';
    }

    


    public function rules()
    {
        return [
            [
                ['archived_at'],
                'integer'
            ],
            [
                [
                    'questionary_id',
                    'type_id'
                ],
                'required'
            ],
            [
                [
                    'questionary_id',
                    'personal_data_id',
                    'passport_data_id',
                    'address_data_id',
                    'type_id',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [
                ['archive'],
                'boolean'
            ],
            [
                ['archive'],
                'default',
                'value' => false
            ],
            [
                [
                    'code',
                    'email'
                ],
                'string',
                'max' => 255
            ],
            [
                ['questionary_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AbiturientQuestionary::class,
                'targetAttribute' => ['questionary_id' => 'id']
            ],
            [
                ['address_data_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ParentAddressData::class,
                'targetAttribute' => ['address_data_id' => 'id']
            ],
            [
                ['type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => FamilyType::class,
                'targetAttribute' => ['type_id' => 'id']
            ],
            [
                ['passport_data_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ParentPassportData::class,
                'targetAttribute' => ['passport_data_id' => 'id']
            ],
            [
                ['personal_data_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ParentPersonalData::class,
                'targetAttribute' => ['personal_data_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'type_id' => Yii::t('abiturient/questionary/parent-data', 'Подпись для поля "type_id" формы "Родитель": `Степень родства`'),
            'typeName' => Yii::t('abiturient/questionary/parent-data', 'Подпись для поля "typeName" формы "Родитель": `Степень родства`'),
            'email' => Yii::t('abiturient/questionary/parent-data', 'Подпись для поля "email" формы "Родитель": `Электронная почта`'),
        ];
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::class,
        ];
    }

    




    public function getAddressData()
    {
        return $this->hasOne(ParentAddressData::class, ['id' => 'address_data_id']);
    }

    




    public function getPassportData()
    {
        return $this->hasOne(ParentPassportData::class, ['id' => 'passport_data_id']);
    }

    




    public function getPersonalData()
    {
        return $this->hasOne(ParentPersonalData::class, ['id' => 'personal_data_id']);
    }

    




    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    




    public function getType()
    {
        return $this->hasOne(FamilyType::class, ['id' => 'type_id']);
    }

    public function getParentRef()
    {
        if (!$this->parent_ref_id && $this->code) {
            $userReference = UserReferenceTypeManager::getUserReferenceFrom1CByGuid($this->code);
            if (isset($userReference)) {
                $this->parent_ref_id = $userReference->id;
                $this->save(true, ['parent_ref_id']);
            }
        }
        return $this->hasOne(StoredUserReferenceType::class, ['id' => 'parent_ref_id']);
    }

    




    public static function checkInterfaceVersion(string $method_name): void
    {
        $valid = false;
        try {
            $result = \Yii::$app->dictionaryManager->GetInterfaceVersion($method_name);
            
            $valid = version_compare($result, '0.0.18.8') >= 0;
        } catch (Throwable $e) {
            \Yii::error("Не удалось получить версию метода {$method_name}: {$e->getMessage()}");
            $valid = false;
        }
        if (!$valid) {
            throw new UserException("Для корректной работы блока родителей необходимо установить все доступные патчи для 1С:Университет ПРОФ");
        }
    }

    public function stringify(): string
    {
        $firstname = ArrayHelper::getValue($this, 'personalData.firstname');
        $middlename = ArrayHelper::getValue($this, 'personalData.middlename');
        $lastname = ArrayHelper::getValue($this, 'personalData.lastname');
        $passportSeries = ArrayHelper::getValue($this, 'passportData.series');
        $passportNumber = ArrayHelper::getValue($this, 'passportData.number');
        return "$lastname $firstname $middlename ($passportSeries $passportNumber)";
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'email',
            'type_id' => function ($model) {
                return ArrayHelper::getValue($model, 'type.name');
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_PARENT_DATA;
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToOneRelationPresenter('addressData', [
                'parent_instance' => $this,
                'child_class' => ParentAddressData::class,
                'parent_column_name' => 'address_data_id',
                'child_column_name' => 'id',
            ]),
            new OneToOneRelationPresenter('passportData', [
                'parent_instance' => $this,
                'child_class' => ParentPassportData::class,
                'parent_column_name' => 'passport_data_id',
                'child_column_name' => 'id',
            ]),
            new OneToOneRelationPresenter('personalData', [
                'parent_instance' => $this,
                'child_class' => ParentPersonalData::class,
                'parent_column_name' => 'personal_data_id',
                'child_column_name' => 'id',
            ]),
        ];
    }

    public function getIdentityString(): string
    {
        $fio = ArrayHelper::getValue($this, 'personalData.fullName');
        $type = ArrayHelper::getValue($this, 'type.uid');
        $personalData = $this->personalData ? $this->personalData->getIdentityString() : '';
        $passportData = $this->passportData ? $this->passportData->getIdentityString() : '';
        $addressData = $this->addressData ? $this->addressData->getIdentityString() : '';
        return "{$type}_{$fio}_{$personalData}_{$passportData}_{$addressData}";
    }

    public function getVirtualProps(): array
    {
        return [
            'typeName' => function (ParentData $model) {
                return ArrayHelper::getValue($model, 'type.name');
            }
        ];
    }

    public function getEntityForDuplicatesFind(): EntityForDuplicatesFind
    {
        $passport = ArrayHelper::getValue($this, 'passportData', null);
        $passport_data = [];
        if ($passport) {
            $passport_data[] = [
                'type' => $passport->documentType,
                'number' => (string)$passport->number,
                'series' => (string)$passport->series,
            ];
        }
        return new EntityForDuplicatesFind(
            (string)ArrayHelper::getValue($this, 'personalData.firstname'),
            (string)ArrayHelper::getValue($this, 'personalData.lastname'),
            (string)ArrayHelper::getValue($this, 'personalData.middlename'),
            (string)ArrayHelper::getValue($this, 'personalData.formated_birthdate'),
            (string)ArrayHelper::getValue($this, 'personalData.snils'),
            $passport_data
        );
    }
}
