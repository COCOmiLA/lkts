<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160505_062829_set_city_id_not_required extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%address_data}}','city_id',$this->integer());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%address_data}}','city_id',$this->integer()->notNull());
        Yii::$app->db->schema->refresh();
    }
}
