<?php

namespace api\modules\moderator\modules\v1\models;

use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantApplication;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class MasterServerHistory extends ActiveRecord
{
    const STATUS_VERIFIED = 1;
    const STATUS_NOT_VERIFIED = 0;

    


    public static function tableName()
    {
        return '{{%entrant_application_master_server_history}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['application_id', 'created_at', 'status'], 'integer'],
            [['status'], 'in', 'range'=>[MasterServerHistory::STATUS_NOT_VERIFIED, MasterServerHistory::STATUS_VERIFIED]],
            [['status'], 'default', 'value' => MasterServerHistory::STATUS_NOT_VERIFIED],
            [['application_id'], 'exist', 'skipOnError' => false, 'targetClass' => EntrantApplication::class, 'targetAttribute' => ['application_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'application_id' => 'Application ID',
            'created_at' => 'Created At',
            'status' => 'Status',
        ];
    }

    




    public function getApplication()
    {
        return $this->hasOne(EntrantApplication::class, ['id' => 'application_id']);
    }
}
