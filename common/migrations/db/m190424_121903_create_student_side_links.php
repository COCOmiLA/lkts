<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190424_121903_create_student_side_links extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190424_113718_student_side_links cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%student_side_links}}', [
            'id'          => $this->primaryKey(),
            'url'         => $this->string(),
            'description' => $this->string(),
            'number'      => $this->integer(),
        ], $tableOptions);

        $this->insert('{{%student_side_links}}', [
            'id'          => 0,
            'number'      => 1,
            'description' => '1С:Университет ПРОФ',
            'url'         => 'https://solutions.1c.ru/catalog/university-prof/features'
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%student_side_links}}');
    }
}
