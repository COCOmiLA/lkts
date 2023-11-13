<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160428_073004_add_area_to_questionary extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%address_data}}', 'area_id', $this->integer());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}', 'area_id');
        Yii::$app->db->schema->refresh();
    }
}
