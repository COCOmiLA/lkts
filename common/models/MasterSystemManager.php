<?php

namespace common\models;

use common\components\EntrantModeratorManager\EntrantModeratorManager;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use yii\web\IdentityInterface;











class MasterSystemManager extends \yii\db\ActiveRecord implements IdentityInterface, IEntrantManager
{
    


    public static function tableName()
    {
        return '{{%master_system_manager}}';
    }

    


    public function rules()
    {
        return [
            [['ref_id'], 'integer'],
            [['full_name'], 'string', 'max' => 1000],
            [['ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredUserReferenceType::class, 'targetAttribute' => ['ref_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'ref_id' => 'Ref ID',
        ];
    }

    




    public function getRef()
    {
        return $this->hasOne(StoredUserReferenceType::class, ['id' => 'ref_id']);
    }

    public static function findIdentity($id)
    {
        return MasterSystemManager::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return MasterSystemManager::find()->one();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return '';
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    public function isTransfer(): bool
    {
        return false;
    }

    




    public function getEntrantManagerEntity(): EntrantManager
    {
        return EntrantModeratorManager::GetOrCreateEntrantModerator($this);
    }

    public function getEntrantManager()
    {
        return $this->hasOne(EntrantManager::class, ['master_system_manager' => 'id']);
    }

    public function isModer(): bool
    {
        return true;
    }
}
