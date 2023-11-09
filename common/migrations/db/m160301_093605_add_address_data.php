<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160301_093605_add_address_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%address_data}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer()->notNull(),
            'country' => $this->string("500")->notNull(),
            'region_id' => $this->integer(),
            'city_id' => $this->integer()->notNull(),
            'street_id' => $this->integer()->notNull(),
            'kladr_code' => $this->string("100")->notNull(),
            'postal_index' => $this->string("100")->notNull(),
            'house_number' => $this->string("100")->notNull(),
            'housing_number' => $this->string("100"),
            'flat_number' => $this->string("100"),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%address_data}}');
        
        Yii::$app->db->schema->refresh();
    }
}
