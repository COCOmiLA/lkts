<?php


namespace common\modules\abiturient\models\bachelor;












class PeriodToSendOriginalEducation extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%periods_of_original_edu_doc_apply}}';
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
            [['campaign_info_id'], 'exist', 'skipOnError' => false, 'targetClass' => CampaignInfo::class, 'targetAttribute' => ['campaign_info_id' => 'id']],
        ];
    }

    public function getCampaignInfo()
    {
        return $this->hasOne(CampaignInfo::class, [
            'id' => 'campaign_info_id'
        ]);
    }
}