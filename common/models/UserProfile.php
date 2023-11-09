<?php

namespace common\models;

use common\components\AttachmentManager;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\models\dictionary\Gender;
use common\models\errors\RecordNotFound;
use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\PersonalData;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;




















class UserProfile extends \yii\db\ActiveRecord
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    public $passportData;
    public $country_id;
    public $contragent;

    


    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    


    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'gender_id'], 'integer'],
            [['firstname', 'middlename', 'lastname', 'birthday'], 'string', 'max' => 255],
            [['passport_series', 'passport_number'], 'string', 'max' => 50],
            ['locale', 'default', 'value' => Yii::$app->language],
            ['locale', 'in', 'range' => Yii::$app->localizationManager->getAvailableLocales()],
            [['passport_type_id', 'passportData', 'country_id', 'gender_id', 'contragent'], 'safe']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('common', 'ID пользователя'),
            'firstname' => Yii::t('common', 'Имя'),
            'middlename' => Yii::t('common', 'Отчество'),
            'lastname' => Yii::t('common', 'Фамилия'),
            'locale' => Yii::t('common', 'Локаль'),
            'gender' => Yii::t('common', 'Пол'),
            'birthday' => 'Дата рождения',
            'passport_series' => 'Серия паспорта',
            'passport_number' => 'Номер паспорта'
        ];
    }

    






    public function initAbiturientQuestionary()
    {
        $user = User::findOne($this->user_id);
        if ($user->isInRole(User::ROLE_ABITURIENT)) {
            if ($user->abiturientQuestionary == null) {
                $abiturientQuestionary = new AbiturientQuestionary();
                $abiturientQuestionary->user_id = $user->id;
                $abiturientQuestionary->status = AbiturientQuestionary::STATUS_CREATED;

                if (!$abiturientQuestionary->save()) {
                    throw new RecordNotValid($abiturientQuestionary);
                }

                $personalData = new PersonalData();
                $personalData->scenario = PersonalData::SCENARIO_REGISTRATION;
                $personalData->questionary_id = $abiturientQuestionary->id;

                $personalData->firstname = $this->firstname;
                $personalData->middlename = $this->middlename;
                $personalData->lastname = $this->lastname;

                $gender = null;
                if ($this->gender_id) {
                    $gender = Gender::findOne($this->gender_id);
                }
                if (!$gender && $this->gender) {
                    $gender = Gender::findByCode($this->gender);
                }
                $personalData->gender_id = ArrayHelper::getValue($gender, 'id');
                $personalData->gender = ArrayHelper::getValue($gender, 'code');

                $personalData->birthdate = $this->birthday;
                $personalData->passport_series = $this->passport_series;
                $personalData->passport_number = $this->passport_number;
                $personalData->country_id = $this->country_id;
                $personalData->getChangeHistoryHandler()->setInitiator($user);

                if (!$personalData->save()) {
                    throw new RecordNotValid($personalData);
                }

                if (!isset($this->passportData)) {
                    throw new RecordNotFound($this);
                }

                if (empty($this->passportData->document_type_id)) {
                    $document_type = CodeSettingsManager::GetEntityByCode('russian_passport_guid');
                    $this->passportData->document_type_id = $document_type->id;
                }
                $this->passportData->questionary_id = $abiturientQuestionary->id;

                $this->passportData->getChangeHistoryHandler()->setInitiator($user);

                if (!$this->passportData->save()) {
                    throw new RecordNotValid($this->passportData);
                }
                AttachmentManager::handleAttachmentUpload([$this->passportData->attachmentCollection]);
            }
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->passport_series != null) {
                $this->passport_series = str_replace(' ', '', $this->passport_series);
            }
            if ($this->passport_number != null) {
                $this->passport_number = str_replace(' ', '', $this->passport_number);
            }

            return true;
        }
        return false;
    }

    


    public function getUser()
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getFullName()
    {
        if ($this->firstname || $this->lastname) {
            return Html::encode(implode(' ', [$this->firstname, $this->lastname]));
        }
        return null;
    }

    


    public function getAbsFullName(): string
    {
        return Html::encode("{$this->lastname} {$this->firstname} {$this->middlename}");
    }
}
