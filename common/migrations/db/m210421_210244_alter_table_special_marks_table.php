<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210421_210244_alter_table_special_marks_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('dictionary_special_marks');
        if(isset($table->columns['full_name'])) {
            $this->alterColumn('{{%dictionary_special_marks}}', 'full_name', $this->string(1000)->null()->defaultValue(null));
        }
    }

    


    public function safeDown()
    {
        return true;
    }

    













}
