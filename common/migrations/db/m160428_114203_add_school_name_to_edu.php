<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160428_114203_add_school_name_to_edu extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%education_data}}', 'school_name', $this->string(1000)->notNull());
        $this->addColumn('{{%education_data}}', 'application_id', $this->integer()->notNull());
        $this->dropForeignKey('fk_education_data_questionary', '{{%education_data}}');
        
        $this->dropColumn('{{%education_data}}', 'questionary_id');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        
        
        $this->dropColumn('{{%education_data}}', 'school_name');
        $this->dropColumn('{{%education_data}}', 'application_id');
        $this->addColumn('{{%education_data}}', 'questionary_id',$this->integer()->notNull());
        $this->addForeignKey('fk_education_data_questionary', '{{%education_data}}', 'questionary_id', '{{%abiturient_questionary}}', 'id', 'cascade', 'cascade');
        Yii::$app->db->schema->refresh();
    }
}
