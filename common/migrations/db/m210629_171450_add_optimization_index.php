<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210629_171450_add_optimization_index extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex('idx_bachelor_egeresult_disc_id_ex_frm_id', 'bachelor_egeresult',['cget_discipline_id', 'cget_exam_form_id']);
        $this->createIndex('idx_cget_entrance_test_disc_id_ex_frm_id', 'cget_entrance_test',['subject_ref_id', 'entrance_test_result_source_ref_id']);
    }

    


    public function safeDown()
    {
        echo "m210629_171450_add_optimization_index cannot be reverted.\n";

        return false;
    }

    













}
