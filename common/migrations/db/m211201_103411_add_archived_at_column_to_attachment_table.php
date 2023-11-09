<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211201_103411_add_archived_at_column_to_attachment_table extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%attachment}}', 'archived_at', $this->integer());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%attachment}}', 'archived_at');
        Yii::$app->db->schema->refresh();
    }
}
