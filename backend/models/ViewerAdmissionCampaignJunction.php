<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class ViewerAdmissionCampaignJunction extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%viewer_admission_campaign_junctions}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'application_type_id'], 'required'],
            [['user_id', 'application_type_id'], 'integer'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('backend', 'Id модератора'),
            'application_type_id' => Yii::t('backend', 'Id приемной кампании'),
        ];
    }
}
