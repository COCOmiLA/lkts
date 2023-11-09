<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m170329_111210_add_form_to_result extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'exam_form_id', $this->integer()->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'exam_form_id');
        
        Yii::$app->db->schema->refresh();
    }
}
