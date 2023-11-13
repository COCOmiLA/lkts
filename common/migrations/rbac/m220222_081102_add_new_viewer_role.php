<?php

use common\rbac\Migration;

class m220222_081102_add_new_viewer_role extends Migration
{
    


    public function safeUp()
    {
        if ($this->auth->getRole('viewer')) {
            return true;
        }

        $newRole = $this->auth->createRole('viewer');
        $this->auth->add($newRole);

        $parentRole = $this->auth->getRole('administrator');
        $this->auth->addChild($parentRole, $newRole);
    }

    


    public function safeDown()
    {
        if ($roleViewer = $this->auth->getRole('viewer')) {
            $this->auth->remove($roleViewer);
        }
    }
}
