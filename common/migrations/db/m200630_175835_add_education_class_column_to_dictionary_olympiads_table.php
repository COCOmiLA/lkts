<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200630_175835_add_education_class_column_to_dictionary_olympiads_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_olympiads}}', 'education_class', $this->string()->null());
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_olympiads}}', 'education_class');
        Yii::$app->db->schema->refresh();
    }
}
