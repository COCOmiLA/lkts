<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160330_063558_add_blocking_status_to_app extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'block_status', $this->integer()->notNull()->defaultValue(0));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}', 'block_status');
        Yii::$app->db->schema->refresh();
    }
}
