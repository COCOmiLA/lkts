<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160701_112901_add_finance_and_form_codes_to_lists extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%competition_list}}', 'finance_code', $this->integer());
        $this->addColumn('{{%competition_list}}', 'learnform_code', $this->integer());
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%competition_list}}', 'finance_code');
        $this->dropColumn('{{%competition_list}}', 'learnform_code');
        
        Yii::$app->db->schema->refresh();
    }
}
