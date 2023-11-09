<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160620_111519_add_village_id extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%address_data}}','village_id', $this->integer());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}','village_id');
        Yii::$app->db->schema->refresh();
    }
}
