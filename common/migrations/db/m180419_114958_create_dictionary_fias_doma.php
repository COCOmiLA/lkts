<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180419_114958_create_dictionary_fias_doma extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable(
            'dictionary_fias_doma',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->defaultValue(null),
                'index' => $this->string(255)->defaultValue(null),
                'code' => $this->string(255)->defaultValue(null),
            ],
            $tableOptions
        );

        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropTable('dictionary_fias_doma');

        \Yii::$app->db->schema->refresh();
    }
}
