<?php

use common\models\User;
use common\rbac\Migration;

class m150625_214101_roles extends Migration
{
    public function up()
    {
        $this->auth->removeAll();

        $abiturient = $this->auth->createRole(User::ROLE_ABITURIENT);
        $this->auth->add($abiturient);
        
        $user = $this->auth->createRole(User::ROLE_USER);
        $this->auth->add($user);

        $student = $this->auth->createRole(User::ROLE_STUDENT);
        $this->auth->add($student);
        
        $teacher = $this->auth->createRole(User::ROLE_TEACHER);
        $this->auth->add($teacher);
        
        $manager = $this->auth->createRole(User::ROLE_MANAGER);
        $this->auth->add($manager);
        $this->auth->addChild($manager, $user);
        $this->auth->addChild($manager, $student);
        $this->auth->addChild($manager, $teacher);
        $this->auth->addChild($manager, $abiturient);

        $admin = $this->auth->createRole(User::ROLE_ADMINISTRATOR);
        $this->auth->add($admin);
        $this->auth->addChild($admin, $manager);

        $this->auth->assign($admin, 1);
        $this->auth->assign($manager, 2);
    }

    public function down()
    {
        $this->auth->remove($this->auth->getRole(User::ROLE_ADMINISTRATOR));
        $this->auth->remove($this->auth->getRole(User::ROLE_MANAGER));
        $this->auth->remove($this->auth->getRole(User::ROLE_USER));
        $this->auth->remove($this->auth->getRole(User::ROLE_STUDENT));
        $this->auth->remove($this->auth->getRole(User::ROLE_TEACHER));
        $this->auth->remove($this->auth->getRole(User::ROLE_ABITURIENT));
    }
}
