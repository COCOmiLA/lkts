<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m151210_120536_add_1c_guid_to_user extends MigrationWithDefaultOptions
{
public function safeUp()
    {
        $this->addColumn('{{%user}}', 'guid', $this->string(255));

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'guid');

        Yii::$app->db->schema->refresh();
    }
}
