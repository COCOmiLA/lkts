<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160630_073444_add_type_and_ia_id_to_exam_result extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%exam_result}}','entrance_test_discipline_id', $this->integer());
        $this->addColumn('{{%exam_result}}','individual_achievement_id', $this->integer());
        $this->addColumn('{{%exam_result}}','is_individual_achievement', $this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%exam_result}}','entrance_test_discipline_id', $this->integer()->notNull());
        $this->dropColumn('{{%exam_result}}','is_individual_achievement');
        $this->dropColumn('{{%exam_result}}','individual_achievement_id');
        
        Yii::$app->db->schema->refresh();
    }
}
