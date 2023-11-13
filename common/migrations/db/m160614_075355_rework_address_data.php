<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160614_075355_rework_address_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%address_data}}', 'city_id', $this->integer());
        $this->alterColumn('{{%address_data}}', 'street_id', $this->integer());
        
        $this->alterColumn('{{%address_data}}', 'kladr_code', $this->string("100"));
        $this->alterColumn('{{%address_data}}', 'postal_index',$this->string("100"));
        
        $this->dropColumn('{{%address_data}}', 'country');
        
        $this->addColumn('{{%address_data}}', 'country_id', $this->integer());
        $this->addColumn('{{%address_data}}', 'homeless', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%address_data}}', 'city', $this->string("255"));
        $this->addColumn('{{%address_data}}', 'street', $this->string("255"));
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_country}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string("255")->notNull(),
            'full_name' => $this->string("1000"),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%address_data}}', 'city_id', $this->integer()->notNull());
        $this->alterColumn('{{%address_data}}', 'street_id', $this->integer()->notNull());
        
        $this->alterColumn('{{%address_data}}', 'kladr_code', $this->string("100")->notNull());
        $this->alterColumn('{{%address_data}}', 'postal_index',$this->string("100")->notNull());
        
        $this->addColumn('{{%address_data}}', 'country', $this->string("500")->notNull());
        
        $this->dropColumn('{{%address_data}}', 'country_id');
        $this->dropColumn('{{%address_data}}', 'homeless');
        $this->dropColumn('{{%address_data}}', 'city');
        $this->dropColumn('{{%address_data}}', 'street');
        
        $this->dropTable('{{%dictionary_country}}');
        Yii::$app->db->schema->refresh();
    }
}
