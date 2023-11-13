<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;













class UserRegistrationConfirmToken extends ActiveRecord
{
    


    public const CODE_LENGTH = 4;

    


    public const STATUS_UNTOUCHED = 1;
    


    public const STATUS_DEPRECATED = 2;

    


    public static function tableName()
    {
        return '{{%user_registration_confirm_token}}';
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
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['confirm_token'], 'string', 'max' => 1000],
            [['confirm_code'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'confirm_token' => 'Confirm Token',
            'confirm_code' => 'Confirm Code',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    




    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function initializeToken(User $user): UserRegistrationConfirmToken {
        $this->user_id = $user->id;
        $this->confirm_token = $this->generateConfirmToken();
        $this->confirm_code = $this->generateConfirmCode();
        $this->status = self::STATUS_UNTOUCHED;
        return $this;
    }

    public function generateConfirmToken(): string {
        return md5($this->user->getPublicIdentity() . time());
    }

    public function generateConfirmCode(): string {
        return bin2hex(random_bytes(self::CODE_LENGTH / 2));
    }

    public function getUrlToConfirm(): string {
        return Url::toRoute(['/user/sign-in/confirm-email-by-link', 'hash' => $this->confirm_token, 'user_id' => $this->user_id]);
    }

    public function isExpired($time): bool {
        $ttl = Yii::$app->configurationManager->getSignupEmailTokenTTL() * 60;
        return $this->created_at + $ttl < $time;
    }
}
