<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160720_072703_convert_to_draw extends MigrationWithDefaultOptions
{
    public function up()
    {
        $this->dropTable('{{%dormitory_list}}');
        $this->dropTable('{{%dormitory_faculty}}');
        $this->dropTable('{{%dormitory_register}}');

        $this->dropTable('{{%dictionary_dormitory_reason}}');

        $this->dropTable('{{%exam_dates}}');
        $this->dropTable('{{%dictionary_exam_reason}}');

        $this->dropTable('{{%dictionary_exam_base}}');
    }

    public function down()
    {
        

        
    }

    









}
