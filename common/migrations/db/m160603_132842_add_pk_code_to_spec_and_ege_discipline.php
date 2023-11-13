<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160603_132842_add_pk_code_to_spec_and_ege_discipline extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%dictionary_ege_discipline}}', 'campaign_code', $this->string(100));
        $this->addColumn('{{%dictionary_speciality}}', 'campaign_code', $this->string(100));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'campaign_code');
        $this->dropColumn('{{%dictionary_speciality}}', 'campaign_code');
        
        Yii::$app->db->schema->refresh();
    }
}
