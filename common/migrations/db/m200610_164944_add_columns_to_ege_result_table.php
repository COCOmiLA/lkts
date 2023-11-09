<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200610_164944_add_columns_to_ege_result_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('dictionary_ege_discipline', 'facultet_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'speciality_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'profil_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'education_level_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'education_form_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'education_program_code', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'discipline_set_id', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'discipline_id', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'discipline_form_id', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'discipline_form_name', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'child_discipline_id', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'child_discipline_name', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'change_discipline_id', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'change_discipline_name', $this->string());
        $this->addColumn('dictionary_ege_discipline', 'minimal_balls', $this->integer());
    }

    public function addColumn($table, $column, $type)
    {
        
        if ($this->db->getTableSchema($table)->getColumn($column) !== null) {
            return;
        }
        parent::addColumn($table, $column, $type);
    }

    


    public function down()
    {
        $this->dropColumn('dictionary_ege_discipline', 'minimal_balls');
        $this->dropColumn('dictionary_ege_discipline', 'change_discipline_name');
        $this->dropColumn('dictionary_ege_discipline', 'change_discipline_id');
        $this->dropColumn('dictionary_ege_discipline', 'child_discipline_name');
        $this->dropColumn('dictionary_ege_discipline', 'child_discipline_id');
        $this->dropColumn('dictionary_ege_discipline', 'discipline_form_name');
        $this->dropColumn('dictionary_ege_discipline', 'discipline_form_id');
        $this->dropColumn('dictionary_ege_discipline', 'discipline_id');
        $this->dropColumn('dictionary_ege_discipline', 'discipline_set_id');
        $this->dropColumn('dictionary_ege_discipline', 'education_program_code');
        $this->dropColumn('dictionary_ege_discipline', 'education_form_code');
        $this->dropColumn('dictionary_ege_discipline', 'education_level_code');
        $this->dropColumn('dictionary_ege_discipline', 'profil_code');
        $this->dropColumn('dictionary_ege_discipline', 'speciality_code');
        $this->dropColumn('dictionary_ege_discipline', 'facultet_code');
    }
}
