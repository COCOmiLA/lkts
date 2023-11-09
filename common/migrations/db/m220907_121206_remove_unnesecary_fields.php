<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220907_121206_remove_unnesecary_fields extends MigrationWithDefaultOptions
{
    public function dropColumn($table, $column)
    {
        
        if ($this->db->getTableSchema($table)->getColumn($column)) {
            parent::dropColumn($table, $column);
        }
    }

    


    public function safeUp()
    {
        \common\modules\abiturient\models\bachelor\ApplicationTypeSettings::deleteAll(['name' => 'blocked']);

        $this->dropColumn('{{%application_type}}', '_hide_ege');
        $this->dropColumn('{{%application_type}}', '_disable_type');
        $this->dropColumn('{{%application_type}}', '_show_list');
        $this->dropColumn('{{%application_type}}', '_hide_ind_ach');
        $this->dropColumn('{{%application_type}}', '_enable_check_ege');
        $this->dropColumn('{{%application_type}}', '_display_speciality_name');
        $this->dropColumn('{{%application_type}}', '_display_group_name');
        $this->dropColumn('{{%application_type}}', '_display_code');
        $this->dropColumn('{{%application_type}}', '_can_see_actual_address');
        $this->dropColumn('{{%application_type}}', '_required_actual_address');
        $this->dropColumn('{{%application_type}}', '_moderator_allowed_to_edit');
        $this->dropColumn('{{%application_type}}', '_persist_moderators_changes_in_sent_application');
        $this->dropColumn('{{%application_type}}', '_archive_actual_app_on_update');
        $this->dropColumn('{{%application_type}}', '_allow_special_requirement_selection');
        $this->dropColumn('{{%application_type}}', '_allow_language_selection');
        $this->dropColumn('{{%application_type}}', '_allow_several_consents');
        $this->dropColumn('{{%application_type}}', '_hide_scans_page');
        $this->dropColumn('{{%application_type}}', '_minify_scans_page');
        $this->dropColumn('{{%application_type}}', '_allow_pick_dates_for_exam');
        $this->dropColumn('{{%application_type}}', '_enable_date_picker_for_exam');
        $this->dropColumn('{{%application_type}}', '_can_change_date_exam_from_1c');
    }

    


    public function safeDown()
    {
        $this->addColumn('{{%application_type}}', '_hide_ege', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_disable_type', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_show_list', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_hide_ind_ach', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_enable_check_ege', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_display_speciality_name', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_display_group_name', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_display_code', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_can_see_actual_address', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_required_actual_address', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_moderator_allowed_to_edit', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_persist_moderators_changes_in_sent_application', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_archive_actual_app_on_update', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_allow_special_requirement_selection', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_allow_several_consents', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_allow_language_selection', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_hide_scans_page', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_allow_pick_dates_for_exam', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_minify_scans_page', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_enable_date_picker_for_exam', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', '_can_change_date_exam_from_1c', $this->boolean()->defaultValue(false));
    }
}
