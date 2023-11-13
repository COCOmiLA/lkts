<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210408_141016_create_special_requirement_reference_type_table extends MigrationWithDefaultOptions
{

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $table = 'special_requirements';

        $this->createTable('{{%' . $table . '}}', [
            'id' => $this->primaryKey(),
            'reference_name' => $this->string(1000)->null(),
            'reference_id' => $this->string(255)->null(),
            'reference_uid' => $this->string(255)->null(),
            'archive' => $this->boolean(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
    }

    


    public function safeDown()
    {
        $table = 'special_requirements';

        if (Yii::$app->db->schema->getTableSchema('{{%' . $table . '}}') !== null) {
            $this->dropTable('{{%' . $table . '}}');

        }
    }
}
