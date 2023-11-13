<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160609_143038_add_reason_id_to_dormitory_exam_registers extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%exam_register}}', 'decline_comment');
        $this->addColumn('{{%exam_register}}', 'decline_reason_id', $this->integer());
        
        $this->dropColumn('{{%dormitory_register}}', 'decline_comment');
        $this->addColumn('{{%dormitory_register}}', 'decline_reason_id', $this->integer());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%exam_register}}', 'decline_reason_id');
        $this->addColumn('{{%exam_register}}', 'decline_comment', $this->string(1000));
        
        $this->dropColumn('{{%dormitory_register}}', 'decline_reason_id');
        $this->addColumn('{{%dormitory_register}}', 'decline_comment', $this->string(1000));
        
        Yii::$app->db->schema->refresh();
    }
}
