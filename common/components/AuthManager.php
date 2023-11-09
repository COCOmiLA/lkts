<?php

namespace common\components;

use yii\rbac\DbManager;

class AuthManager extends DbManager
{
    
    private $memorizedRoles = [];
    
    public function getRolesByUser($userId)
    {
        if (!isset($this->memorizedRoles[$userId])) {
            $this->memorizedRoles[$userId] = parent::getRolesByUser($userId);
        }
        
        return $this->memorizedRoles[$userId];
    }
    
    public function assign($role, $userId)
    {
        $this->resetCachedRoles();
        return parent::assign($role, $userId);
    }
    
    public function revoke($role, $userId)  
    {
        $this->resetCachedRoles();
        return parent::revoke($role, $userId);
    }
    
    public function revokeAll($userId)
    {
        $this->resetCachedRoles();
        return parent::revokeAll($userId);
    }
    
    public function resetCachedRoles()
    {
        $this->memorizedRoles = [];
    }
}
