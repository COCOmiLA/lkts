<?php

use backend\models\PodiumRoleRule;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\User;




class m201002_124513_creat_teble_for_forum_role_rule extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%podium_role_rule}}',
            [
                'id' => $this->primaryKey(),
                'role' => $this->string()->defaultValue(''),
                'rule' => $this->boolean()->defaultValue(PodiumRoleRule::ENABLE),
            ],
            $tableOptions
        );

        $allRole = User::getAllRole();
        if (!empty($allRole)) {
            foreach($allRole as $role) {
                $this->insert('{{%podium_role_rule}}', ['role' => $role]);
            }
        }
    }

    


    public function safeDown()
    {
        $this->dropTable('podium_role_rule');
    }
}