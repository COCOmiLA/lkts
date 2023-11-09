<?php


namespace common\modules\abiturient\models\bachelor;
















class PeriodToSendAgreement extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%periods_to_send_agreement}}';
    }

    public function rules()
    {
        return [
            [[
                'start',
                'end',
                'campaign_info_id',
            ], 'required'],
            [['campaign_info_id'], 'integer'],
            [['start', 'end'], 'string'],
            [['in_day_of_sending_app_only', 'in_day_of_sending_speciality_only'], 'boolean'],
            [['campaign_info_id'], 'exist', 'skipOnError' => false, 'targetClass' => CampaignInfo::class, 'targetAttribute' => ['campaign_info_id' => 'id']],
        ];
    }

    public function getCampaignInfo()
    {
        return $this->hasOne(CampaignInfo::class, [
            'id' => 'campaign_info_id'
        ]);
    }

    public function getAdditionalConditionsDescription()
    {
        $result = '';
        if ($this->in_day_of_sending_app_only) {
            $result .= 'только в день приёма первого заявления, ';
        }
        if ($this->in_day_of_sending_speciality_only) {
            $result .= 'только в день приёма заявления по конкурсу, ';
        }
        return trim((string)$result, ", \t\n\r\0\x0B");
    }
}