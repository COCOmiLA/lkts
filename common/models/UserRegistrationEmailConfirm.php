<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;











class UserRegistrationEmailConfirm extends ActiveRecord
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_DEPRECATED = 0;

    


    public static function tableName()
    {
        return '{{%user_registration_email_confirm}}';
    }


    


    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    


    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status'
        ];
    }

    




    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
