<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Exception as DbException;







class m210124_113032_add_exam_from_id_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%bachelor_egeresult}}');
        if (!isset($table->columns['exam_form_id'])) {
            $this->addColumn('{{%bachelor_egeresult}}', 'exam_form_id', $this->integer()->null());
        }
        
        try {
            
            $this->createIndex(
                '{{%idx-bachelor_egeresult-exam_form_id}}',
                '{{%bachelor_egeresult}}',
                'exam_form_id'
            );
        } catch (DbException $e) {
            \Yii::error("При применении миграции возникла ошибка: " . $e->getMessage());
        }
        
        if (!isset($table->foreignKeys['fk-bachelor_egeresult-exam_form_id'])) {
            
            $this->addForeignKey(
                '{{%fk-bachelor_egeresult-exam_form_id}}',
                '{{%bachelor_egeresult}}',
                'exam_form_id',
                '{{%dictionary_discipline_form}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-exam_form_id}}',
            '{{%bachelor_egeresult}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_egeresult-exam_form_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropColumn('{{%bachelor_egeresult}}', 'exam_form_id');
    }
}
