<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m180119_114250_update_passport_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_passport_data_citizenship', '{{%passport_data}}');
        $this->dropColumn('{{%passport_data}}', 'citizenship_id');
        $this->addColumn('{{%passport_data}}', 'country_id', $this->integer());
        $this->addForeignKey('fk_passport_data_country', '{{%passport_data}}', 'country_id', '{{%dictionary_country}}', 'id', 'restrict', 'restrict');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_passport_data_country', '{{%passport_data}}');
        $this->dropColumn('{{%passport_data}}', 'country_id');
        $this->addColumn('{{%passport_data}}', 'citizenship_id', $this->integer());
        $this->addForeignKey('fk_passport_data_citizenship', '{{%passport_data}}', 'citizenship_id', '{{%dictionary_citizenship}}', 'id', 'restrict', 'restrict');
    }
}
