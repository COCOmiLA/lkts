<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m171229_070510_update_dictionaries_add_archive_column extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $dictionaries = [
            '{{%dictionary_admission_base}}',
            '{{%dictionary_admission_categories}}',
            '{{%dictionary_admission_features}}',
            '{{%dictionary_allowed_forms}}',
            '{{%dictionary_citizenship}}',
            '{{%dictionary_country}}',
            '{{%dictionary_dicipline_allowed_forms}}',
            '{{%dictionary_document_shipment}}',
            '{{%dictionary_document_type}}',
            '{{%dictionary_document_view}}',
            '{{%dictionary_educational_inst_type}}',
            '{{%dictionary_education_info}}',
            '{{%dictionary_education_level}}',
            '{{%dictionary_education_type}}',
            '{{%dictionary_ege_discipline}}',
            '{{%dictionary_entrance_test_discipline}}',
            '{{%dictionary_fias}}',
            '{{%dictionary_foreign_languages}}',
            '{{%dictionary_individual_achievement}}',
            '{{%dictionary_olympiads}}',
            '{{%dictionary_ownage_form}}',
            '{{%dictionary_privileges}}',
            '{{%dictionary_speciality}}',
            '{{%dictionary_special_marks}}',
            '{{%admission_campaign}}',
            '{{%application_type}}',
            '{{%campaign_info}}',
        ];

        foreach ($dictionaries as $d) {
            $this->addColumn($d, 'archive', $this->boolean()->defaultValue(false));
        }
    }

    public function safeDown()
    {
        $dictionaries = [
            '{{%dictionary_admission_base}}',
            '{{%dictionary_admission_categories}}',
            '{{%dictionary_admission_features}}',
            '{{%dictionary_allowed_forms}}',
            '{{%dictionary_citizenship}}',
            '{{%dictionary_country}}',
            '{{%dictionary_dicipline_allowed_forms}}',
            '{{%dictionary_document_shipment}}',
            '{{%dictionary_document_type}}',
            '{{%dictionary_document_view}}',
            '{{%dictionary_educational_inst_type}}',
            '{{%dictionary_education_info}}',
            '{{%dictionary_education_level}}',
            '{{%dictionary_education_type}}',
            '{{%dictionary_ege_discipline}}',
            '{{%dictionary_entrance_test_discipline}}',
            '{{%dictionary_fias}}',
            '{{%dictionary_foreign_languages}}',
            '{{%dictionary_individual_achievement}}',
            '{{%dictionary_olympiads}}',
            '{{%dictionary_ownage_form}}',
            '{{%dictionary_privileges}}',
            '{{%dictionary_speciality}}',
            '{{%dictionary_special_marks}}',
            '{{%admission_campaign}}',
            '{{%application_type}}',
            '{{%campaign_info}}',
        ];

        foreach ($dictionaries as $d) {
            $this->dropColumn($d, 'archive');
        }
    }
}
