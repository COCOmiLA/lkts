<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m170330_121303_set_unrequired_points_for_exam_result_in_Db extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100)->notNull());
        
        Yii::$app->db->schema->refresh();
    }
}
