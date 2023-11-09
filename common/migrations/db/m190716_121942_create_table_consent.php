<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190716_121942_create_table_consent extends MigrationWithDefaultOptions
{
    public function up()
    {
        if ($this->db->getTableSchema('{{%consent}}', true) !== null) {
            $this->dropTable('{{%consent}}');
        }

        $this->createTable(
            '{{%consent}}',
            [
                'id' => $this->primaryKey(),
                'enable_consent' => $this->integer()->notNull()->defaultValue(0),
                'enable_sandbox' => $this->integer()->notNull()->defaultValue(0)
            ]
        );

        $this->insert(
            '{{%consent}}',
            [
                'enable_consent' => 0,
                'enable_sandbox' => 0
            ]
        );

        Yii::$app->db->schema->refresh();
    }

    public function down()
    {
        $this->dropTable('{{%consent}}');
        Yii::$app->db->schema->refresh();
    }
}
