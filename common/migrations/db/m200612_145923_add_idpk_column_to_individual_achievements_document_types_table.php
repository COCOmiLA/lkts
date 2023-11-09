<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200612_145923_add_idpk_column_to_individual_achievements_document_types_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievements_document_types}}', 'campaign_code', $this->string());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievements_document_types}}', 'campaign_code');
    }
}
