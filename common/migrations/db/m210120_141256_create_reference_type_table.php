<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210120_141256_create_reference_type_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tables = [
            '',
            'user',
            'discipline',
            'admission_campaign',
            'campaign_info',
            'discipline_form',
            'subdivision',
            'direction',
            'profile',
            'education_level',
            'education_form',
            'education_program',
            'competitive_group',
            'subject_set',
            'education_source',
            'budget_level',
            'detail_group',
            'achievement_category',
            'achievement_document_type',
            'curriculum',
            'variant_of_retest',
            'olympic',
            'olympic_type',
            'olympic_level',
            'olympic_class',
            'olympic_kind',
            'olympic_profile',
        ];

        foreach ($tables as $table) {
            $this->createReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type', $tableOptions);
        }

    }

    


    public function safeDown()
    {
        $tables = [

            '',
            'user',
            'discipline',
            'admission_campaign',
            'campaign_info',
            'discipline_form',
            'subdivision',
            'direction',
            'profile',
            'education_level',
            'education_form',
            'education_program',
            'competitive_group',
            'subject_set',
            'education_source',
            'budget_level',
            'detail_group',
            'achievement_category',
            'achievement_document_type',
            'curriculum',
            'variant_of_retest',
            'olympic',
            'olympic_type',
            'olympic_level',
            'olympic_class',
            'olympic_kind',
            'olympic_profile',
        ];

        foreach ($tables as $table) {
            if (Yii::$app->db->schema->getTableSchema('{{%' . (empty($table) ? '' : $table . '_') . 'reference_type}}') !== null) {
                $this->dropReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type');
            }
        }
    }
}
