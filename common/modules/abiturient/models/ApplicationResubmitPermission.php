<?php

namespace common\modules\abiturient\models;

use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;










class ApplicationResubmitPermission extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%resubmit_permissions}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'type_id'], 'required'],
            [['user_id', 'type_id'], 'integer'],
            [['allow'], 'boolean'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApplicationType::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getApplicationType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ApplicationType::class, ['id' => 'type_id']);
    }
}