<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160620_131837_add_notfound_check extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%address_data}}', 'not_found', $this->smallInteger()->defaultValue(0));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}', 'not_found');
        Yii::$app->db->schema->refresh();
    }
}
