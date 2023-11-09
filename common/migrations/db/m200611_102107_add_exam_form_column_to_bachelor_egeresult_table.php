<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200611_102107_add_exam_form_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'exam_form', $this->string());
    }

    


    public function down()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'exam_form');
    }
}
