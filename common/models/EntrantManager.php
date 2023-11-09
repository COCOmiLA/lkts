<?php

namespace common\models;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use yii\db\ActiveRecord;











class EntrantManager extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%entrant_manager}}';
    }

    


    public function rules()
    {
        return [
            [['master_system_manager', 'local_manager'], 'integer'],
            [['local_manager'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['local_manager' => 'id']],
            [['master_system_manager'], 'exist', 'skipOnError' => true, 'targetClass' => MasterSystemManager::class, 'targetAttribute' => ['master_system_manager' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'master_system_manager' => 'Master System Manager',
            'local_manager' => 'Local Manager',
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        ChangeHistory::updateAll(['entrant_manager_id' => null], ['entrant_manager_id' => $this->id]);
        return true;
    }

    




    public function getLocalManager()
    {
        return $this->hasOne(User::class, ['id' => 'local_manager']);
    }

    




    public function getMasterSystemManager()
    {
        return $this->hasOne(MasterSystemManager::class, ['id' => 'master_system_manager']);
    }

    public function getManagerName()
    {
        if ($this->localManager !== null) {
            return $this->localManager->username;
        }
        if ($this->masterSystemManager !== null) {
            return "{$this->masterSystemManager->full_name} (1C)";
        }
        return null;
    }

    public function isLocalManager()
    {
        return (bool)$this->localManager;
    }
}
