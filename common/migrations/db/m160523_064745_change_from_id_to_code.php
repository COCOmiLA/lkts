<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160523_064745_change_from_id_to_code extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%exam_dates}}', 'discipline_id');
        $this->addColumn('{{%exam_dates}}','discipline_code',$this->string(1000)->notNull());
        
        $this->dropColumn('{{%consult_dates}}', 'discipline_id');
        $this->addColumn('{{%consult_dates}}','discipline_code', $this->string(1000)->notNull());
        
        $this->delete('{{%exam_dates}}');
        $this->delete('{{%consult_dates}}');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%exam_dates}}', 'discipline_code');
        $this->addColumn('{{%exam_dates}}','discipline_id',$this->integer()->notNull());
        $this->delete('{{%exam_dates}}');
        
        $this->dropColumn('{{%consult_dates}}', 'discipline_code');
        $this->addColumn('{{%consult_dates}}','discipline_id',$this->integer()->notNull());
        $this->delete('{{%consult_dates}}');

        Yii::$app->db->schema->refresh();
    }
}
