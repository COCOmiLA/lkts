<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160627_115241_add_fields_to_competition extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete('{{%competition_list_rows}}');
        $this->delete('{{%competition_list}}');
        
        $this->addColumn('{{%competition_list}}','speciality_system_code',$this->integer());
        $this->addColumn('{{%competition_list}}','campaign_code',$this->integer());
        
        $this->addColumn('{{%competition_list_rows}}','user_guid',$this->string());
        $this->addColumn('{{%competition_list_rows}}','group_code',$this->integer());
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%competition_list}}','speciality_system_code');
        $this->dropColumn('{{%competition_list}}','campaign_code');
        
        $this->dropColumn('{{%competition_list_rows}}','user_guid');
        $this->dropColumn('{{%competition_list_rows}}','group_code');
        
        Yii::$app->db->schema->refresh();
    }
}
