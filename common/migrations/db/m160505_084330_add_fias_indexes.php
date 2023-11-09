<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160505_084330_add_fias_indexes extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->createIndex('type_region', '{{%dictionary_fias}}', 'address_element_type, region_code');
        $this->createIndex('type_region_area', '{{%dictionary_fias}}', 'address_element_type, region_code, area_code');
        $this->createIndex('type_region_area_city', '{{%dictionary_fias}}', 'address_element_type, region_code, area_code, city_code');
        $this->createIndex('type_region_area_village', '{{%dictionary_fias}}', 'address_element_type, region_code, area_code, village_code');
        $this->createIndex('type_region_city', '{{%dictionary_fias}}', 'address_element_type, region_code, city_code');
        $this->createIndex('type_region_village', '{{%dictionary_fias}}', 'address_element_type, region_code, village_code');
        $this->createIndex('kladr_code', '{{%dictionary_fias}}', 'code', true);
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropIndex('type_region', '{{%dictionary_fias}}');
        $this->dropIndex('type_region_area', '{{%dictionary_fias}}');
        $this->dropIndex('type_region_area_city', '{{%dictionary_fias}}');
        $this->dropIndex('type_region_area_village', '{{%dictionary_fias}}');
        $this->dropIndex('type_region_city', '{{%dictionary_fias}}');
        $this->dropIndex('type_region_village', '{{%dictionary_fias}}');
        $this->dropIndex('kladr_code', '{{%dictionary_fias}}');
        Yii::$app->db->schema->refresh();
    }
}
