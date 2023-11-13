<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190301_075146_rolerule_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190301_075146_rolerule_table cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('rolerule', [
            'id' => $this->primaryKey(),
            'student' => $this->integer()->notNull()->defaultValue(1),
            'teacher' => $this->integer()->notNull()->defaultValue(1),
            'abiturient' => $this->integer()->notNull()->defaultValue(1)
        ]);

        $this->insert('rolerule', [
            'student' => '1',
            'teacher' => '1',
            'abiturient' => '1'
        ]);
    }

    


    public function down()
    {
        $this->dropTable('rolerule');
    }

}
