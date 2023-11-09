<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160610_120654_add_fields_to_questionary extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%personal_data}}', 'need_dormitory', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%personal_data}}', 'need_pc_course', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%personal_data}}', 'need_po_course', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%personal_data}}', 'need_engineer_class', $this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%personal_data}}', 'need_dormitroy');
        $this->dropColumn('{{%personal_data}}', 'need_pc_course');
        $this->dropColumn('{{%personal_data}}', 'need_po_course');
        $this->dropColumn('{{%personal_data}}', 'need_engineer_class');
        
        Yii::$app->db->schema->refresh();
    }
}
