<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160615_085108_address_data_hot_fix extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%address_data}}', 'city', $this->string("255"));
        $this->dropColumn('{{%address_data}}', 'street', $this->string("255"));
        
        $this->addColumn('{{%address_data}}', 'city_name', $this->string("255"));
        $this->addColumn('{{%address_data}}', 'street_name', $this->string("255"));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}', 'city_name', $this->string("255"));
        $this->dropColumn('{{%address_data}}', 'street_name', $this->string("255"));
        
        $this->addColumn('{{%address_data}}', 'city', $this->string("255"));
        $this->addColumn('{{%address_data}}', 'street', $this->string("255"));
        
        Yii::$app->db->schema->refresh();
    }
}
