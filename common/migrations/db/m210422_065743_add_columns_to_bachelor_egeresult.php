<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210422_065743_add_columns_to_bachelor_egeresult extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('bachelor_egeresult', 'cget_exam_form_id', $this->integer()->defaultValue(null));
        $this->addColumn('bachelor_egeresult', 'cget_discipline_id', $this->integer()->defaultValue(null));
        $this->addColumn('bachelor_egeresult', 'cget_child_discipline_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'FK_for_cget_exam_form_id',
            'bachelor_egeresult',
            'cget_exam_form_id',
            'discipline_form_reference_type',
            'id'
        );
        $this->addForeignKey(
            'FK_for_cget_discipline_id',
            'bachelor_egeresult',
            'cget_discipline_id',
            'discipline_reference_type',
            'id'
        );
        $this->addForeignKey(
            'FK_for_cget_child_discipline_id',
            'bachelor_egeresult',
            'cget_child_discipline_id',
            'discipline_reference_type',
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_for_cget_exam_form_id', 'bachelor_egeresult');
        $this->dropForeignKey('FK_for_cget_discipline_id', 'bachelor_egeresult');
        $this->dropForeignKey('FK_for_cget_child_discipline_id', 'bachelor_egeresult');

        $this->dropColumn('bachelor_egeresult', 'cget_exam_form_id');
        $this->dropColumn('bachelor_egeresult', 'cget_discipline_id');
        $this->dropColumn('bachelor_egeresult', 'cget_child_discipline_id');
    }
}
