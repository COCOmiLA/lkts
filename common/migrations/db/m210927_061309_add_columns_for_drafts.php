<?php

use yii\db\Migration;




class m210927_061309_add_columns_for_drafts extends Migration
{
    private $archivable_tables = [
        'bachelor_application',
        'abiturient_questionary',
        'education_data',
        'individual_achievement',
        'individual_achievements_document_types',
        'passport_data',
        'admission_agreement',
        'agreement_decline',
        'bachelor_preferences',
        'bachelor_speciality',
        'bachelor_target_reception',
        'parent_data',
    ];

    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'draft_status', $this->integer());
        $this->addColumn('{{%abiturient_questionary}}', 'draft_status', $this->integer());

        foreach ($this->archivable_tables as $archivable_table) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $archivable_table . '}}')->getColumn('archived_at')) {
                $this->addColumn('{{%' . $archivable_table . '}}', 'archived_at', $this->integer());
            }
            
            if ($archivable_table == 'individual_achievement') {
                continue;
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $archivable_table . '}}')->getColumn('archive')) {
                $this->addColumn('{{%' . $archivable_table . '}}', 'archive', $this->boolean()->defaultValue(false));
            }
        }
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%abiturient_questionary}}', 'draft_status');
        $this->dropColumn('{{%bachelor_application}}', 'draft_status');

        foreach ($this->archivable_tables as $archivable_table) {
            if (Yii::$app->db->schema->getTableSchema('{{%' . $archivable_table . '}}')->getColumn('archive')) {
                $this->dropColumn('{{%' . $archivable_table . '}}', 'archive');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $archivable_table . '}}')->getColumn('archived_at')) {
                $this->dropColumn('{{%' . $archivable_table . '}}', 'archived_at');
            }
        }
        Yii::$app->db->schema->refresh();
    }

}
