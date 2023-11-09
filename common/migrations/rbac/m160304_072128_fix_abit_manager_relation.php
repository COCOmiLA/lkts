<?php

use common\rbac\Migration;

class m160304_072128_fix_abit_manager_relation extends Migration
{
    public function up()
    {
        $managerRole = $this->auth->getRole(\common\models\User::ROLE_MANAGER);
        $abitRole = $this->auth->getRole(\common\models\User::ROLE_ABITURIENT);
        
        $status = $this->auth->removeChild($managerRole, $abitRole);
        echo $status;
    }

    public function down()
    {
        $managerRole = $this->auth->getRole(\common\models\User::ROLE_MANAGER);
        $abitRole = $this->auth->getRole(\common\models\User::ROLE_ABITURIENT);
        
        $this->auth->addChild($managerRole, $abitRole);
    }
}
