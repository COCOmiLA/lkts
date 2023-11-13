<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\models\traits\ArchiveTrait;
use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;






class AdmissionAgreementToDelete extends \yii\db\ActiveRecord
{
    use ArchiveTrait;

    public static function tableName()
    {
        return '{{%admission_agreement_to_delete}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    


    public function rules()
    {
        return [
            [['user_id', 'agreement_id'], 'integer'],
            [['archive'], 'boolean'],
            [['campaign_code', 'application_code'], 'string'],
            [['user_id', 'agreement_id', 'campaign_code', 'application_code'], 'required'],
        ];
    }

    


    public function getUser()
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAgreement()
    {
        return $this->hasOne(AdmissionAgreement::class, ['id' => 'agreement_id']);
    }

    public function getAgreementDecline()
    {
        return $this->getRawAgreementDecline()
            ->active();
    }

    public function getRawAgreementDecline()
    {
        return $this->hasOne(AgreementDecline::class, ['agreement_id' => 'id'])
            ->via('agreement')
            ->orderBy([AgreementDecline::tableName() . '.archive' => SORT_ASC]);
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }
}
