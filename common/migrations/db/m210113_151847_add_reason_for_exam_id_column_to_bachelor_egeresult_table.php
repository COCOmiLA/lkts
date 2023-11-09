<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210113_151847_add_reason_for_exam_id_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'reason_for_exam_id', $this->integer());

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'reason_for_exam_id');
        Yii::$app->db->schema->refresh();
    }
}
