<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210302_101635_replace_edu_program_with_edu_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        \Yii::$app->db->createCommand("UPDATE dictionary_ege_discipline SET education_program_ref_id=null")
            ->execute();
        $this->dropForeignKey(
            '{{%fk-dictionary_ege_discipline-education_program_ref_id}}',
            '{{%dictionary_ege_discipline}}'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_ege_discipline-education_program_ref_id}}',
            '{{%dictionary_ege_discipline}}',
            'education_program_ref_id',
            '{{%dictionary_education_type}}',
            'id',
            'NO ACTION'
        );

        \common\models\dictionary\Speciality::updateAll(['education_program_ref_id' => null]);
        $this->dropForeignKey(
            '{{%fk-dictionary_speciality-education_program_ref_id}}',
            '{{%dictionary_speciality}}'
        );
        $this->addForeignKey(
            '{{%fk-dictionary_speciality-education_program_ref_id}}',
            '{{%dictionary_speciality}}',
            'education_program_ref_id',
            '{{%dictionary_education_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-dictionary_ege_discipline-education_program_ref_id}}',
            '{{%dictionary_ege_discipline}}'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_ege_discipline-education_program_ref_id}}',
            '{{%dictionary_ege_discipline}}',
            'education_program_ref_id',
            '{{%education_program_reference_type}}',
            'id',
            'NO ACTION'
        );

        $this->dropForeignKey(
            '{{%fk-dictionary_speciality-education_program_ref_id}}',
            '{{%dictionary_speciality}}'
        );
        $this->addForeignKey(
            '{{%fk-dictionary_speciality-education_program_ref_id}}',
            '{{%dictionary_speciality}}',
            'education_program_ref_id',
            '{{%education_program_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

}
