<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160621_062849_add_region_area_town extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%address_data}}', 'region_name', $this->string("255"));
        $this->addColumn('{{%address_data}}', 'area_name', $this->string("255"));
        $this->addColumn('{{%address_data}}', 'town_name', $this->string("255"));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}', 'region_name');
        $this->dropColumn('{{%address_data}}', 'area_name');
        $this->dropColumn('{{%address_data}}', 'town_name');
        
        Yii::$app->db->schema->refresh();
    }
}
