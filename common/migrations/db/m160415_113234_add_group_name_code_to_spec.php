<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160415_113234_add_group_name_code_to_spec extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'group_code', $this->string(100)->notNull());
        $this->addColumn('{{%dictionary_speciality}}', 'group_name', $this->string(1000)->notNull());
        $this->addColumn('{{%dictionary_speciality}}', 'speciality_human_code', $this->string(100)->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_speciality}}', 'group_code');
        $this->dropColumn('{{%dictionary_speciality}}', 'group_name');
        $this->dropColumn('{{%dictionary_speciality}}', 'speciality_human_code');
        
        Yii::$app->db->schema->refresh();
    }
}
