<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200623_065702_add_settings_column_to_admission_campaign_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'display_speciality_name', $this->boolean()->null());
        $this->addColumn('{{%application_type}}', 'display_group_name', $this->boolean()->null());
        $this->addColumn('{{%application_type}}', 'display_code', $this->boolean()->null());
        \common\modules\abiturient\models\bachelor\ApplicationType::updateAll([
            'display_speciality_name' => 1,
            'display_code' => 1
        ], ['archive' => false]);
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'display_speciality_name');
        $this->dropColumn('{{%application_type}}', 'display_group_name');
        $this->dropColumn('{{%application_type}}', 'display_code');
        Yii::$app->db->schema->refresh();
    }
}
