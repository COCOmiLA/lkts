<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\User;




class m200921_123540_creat_table_for_sorted_element_page extends MigrationWithDefaultOptions
{
    private $tableName = '{{%sorted_element_page}}';

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->getTableSchema($this->tableName, true) === null) {
            $this->createTable(
                $this->tableName,
                [
                    'id' => $this->primaryKey(),

                    'number' => $this->integer()->defaultValue(0),
                    'url' => $this->string(1024)->defaultValue(''),
                    'description' => $this->string(1024)->defaultValue(''),
                    'role' => $this->string(512)->defaultValue(User::ROLE_STUDENT),
                    'place' => $this->string(512)->defaultValue(User::ROLE_STUDENT),
                    'is_removed' => $this->boolean()->defaultValue(false),

                    'created_at' => $this->integer(),
                    'updated_at' => $this->integer(),
                ],
                $tableOptions
            );
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        if ($this->db->getTableSchema($this->tableName, true) !== null) {
            $this->dropTable($this->tableName);
        }
    }
}
