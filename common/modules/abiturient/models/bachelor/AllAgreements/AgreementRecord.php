<?php

namespace common\modules\abiturient\models\bachelor\AllAgreements;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;










class AgreementRecord extends \yii\db\ActiveRecord
{
    const AGREEMENT_TYPE_AGREED = 'Agreement';
    const AGREEMENT_TYPE_WITHDRAW = 'Withdraw';

    public static function tableName()
    {
        return '{{%agreement_records}}';
    }

    public function rules()
    {
        return [
            [[
                'type',
                'date',
                'speciality_name',
                'speciality_guid',
            ], 'required'],
            [[
                'type',
                'speciality_name',
                'speciality_guid',
            ], 'string'],
            [[
                'date',
                'application_id',
            ], 'integer'],
            [
                'type',
                'in',
                'range' => [
                    self::AGREEMENT_TYPE_AGREED,
                    self::AGREEMENT_TYPE_WITHDRAW
                ]
            ],
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getSpeciality()
    {
        return $this->hasOne(BachelorSpeciality::class, [
            'application_code' => 'speciality_guid',
            'application_id' => 'application_id',
        ]);
    }

    public function getTypeDescription()
    {
        switch ($this->type) {
            case self::AGREEMENT_TYPE_WITHDRAW:
                return 'Отозвано согласие на зачисление';
            case self::AGREEMENT_TYPE_AGREED:
                return 'Подано согласие на зачисление';
            default:
                return '';
        }
    }

    public function getFormattedDate()
    {
        return date('d.m.Y H:i:s', $this->date);
    }
}