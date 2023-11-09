<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210115_153718_add_required_fk_constraints extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            '{{%idx-bachelor_egeresult-reason_for_exam_id}}',
            '{{%bachelor_egeresult}}',
            'reason_for_exam_id'
        );
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-reason_for_exam_id}}',
            '{{%bachelor_egeresult}}',
            'reason_for_exam_id',
            '{{%dictionary_reasons_for_exam}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}',
            'language_id',
            '{{%dictionary_foreign_languages}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}'
        );
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-reason_for_exam_id}}',
            '{{%bachelor_egeresult}}'
        );
        $this->dropIndex(
            '{{%idx-bachelor_egeresult-reason_for_exam_id}}',
            '{{%bachelor_egeresult}}'
        );
    }

}
