<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200727_084614_resolve_exam_form_id_nullable extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('bachelor_egeresult');
        if(isset($table->columns['exam_form_id'])) {
            $this->alterColumn('{{%bachelor_egeresult}}', 'exam_form_id', $this->integer(11)->null()->defaultValue(null));
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
