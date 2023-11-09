<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160304_082147_add_date_sended_to_application extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'sended_at', $this->integer());

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}', 'sended_at');

        Yii::$app->db->schema->refresh();
    }
}
