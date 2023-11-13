<?php

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use yii\db\Migration;




class m211004_114129_create_dictionary_predmet_of_exams_schedule_table extends Migration
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%dictionary_predmet_of_exams_schedule}}',
            [
                'id' => $this->primaryKey(),

                'campaign_ref_id' => $this->integer()->defaultValue(null),
                'curriculum_ref_id' => $this->integer()->defaultValue(null),
                'finance_ref_id' => $this->integer()->defaultValue(null),
                'group_ref_id' => $this->integer()->defaultValue(null),
                'subject_ref_id' => $this->integer()->defaultValue(null),
                'form_ref_id' => $this->integer()->defaultValue(null),
                'predmet_guid' => $this->string(100)->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),

                'created_at' => $this->integer()->defaultValue(null),
                'updated_at' => $this->integer()->defaultValue(null),
            ],
            $tableOptions
        );

        $fkList = [
            'curriculum_ref' => StoredCurriculumReferenceType::tableName(),
            'subject_ref'    => StoredDisciplineReferenceType::tableName(),
            'form_ref'       => StoredDisciplineFormReferenceType::tableName(),
            'finance_ref'    => StoredEducationSourceReferenceType::tableName(),
            'group_ref'      => StoredCompetitiveGroupReferenceType::tableName(),
            'campaign_ref'   => StoredAdmissionCampaignReferenceType::tableName(),
        ];

        foreach ($fkList as $column => $table) {
            $this->addForeignKey(
                "FK_to_{$column}_for_poes", 
                '{{%dictionary_predmet_of_exams_schedule}}',
                "{$column}_id",
                $table,
                'id'
            );
        }
    }

    


    public function safeDown()
    {
        $fkList = [
            'form_ref',
            'group_ref',
            'finance_ref',
            'predmet_ref',
            'campaign_ref',
            'curriculum_ref',
        ];

        foreach ($fkList as $column) {
            $this->dropForeignKey("FK_to_{$column}_for_poes", '{{%dictionary_predmet_of_exams_schedule}}');
        }

        $this->dropTable('{{%dictionary_predmet_of_exams_schedule}}');
    }
}
