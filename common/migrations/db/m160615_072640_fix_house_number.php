<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160615_072640_fix_house_number extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%address_data}}', 'house_number', $this->string("100"));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%address_data}}', 'house_number', $this->string("100")->notNull());
        Yii::$app->db->schema->refresh();
    }
    
}
