<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160331_092603_add_filename_to_attachment extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%attachment}}', 'filename', $this->string(255));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%attachment}}', 'filename', $this->string(255));
        Yii::$app->db->schema->refresh();
    }
}
