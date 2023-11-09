<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160415_080036_add_exam_register_to_ege_result extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100));
        $this->addColumn('{{%bachelor_egeresult}}', 'exam_register', $this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'exam_register');
        $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100)->notNull());
        
        Yii::$app->db->schema->refresh();
    }
}
