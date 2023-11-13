<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160229_125348_add_fias_dictionary extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dictionary_fias}}', [
            'id' => $this->primaryKey(),
            'address_element_type' => $this->string(255)->notNull(),
            'region_code' => $this->string(100)->notNull(),
            'area_code' => $this->string(100)->notNull(),
            'city_code' => $this->string(100)->notNull(),
            'village_code' => $this->string(100)->notNull(),
            'street_code' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(1000)->notNull(),
            'short' => $this->string(100),
            'zip_code' => $this->string(100),
            'alt_name' => $this->string(1000),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_fias}}');
        
        Yii::$app->db->schema->refresh();
    }
}
