<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230112_123714_create_main_page_setting_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%main_page_setting}}';

    


    public function safeUp()
    {
        if ($this->db->getTableSchema(self::TN, true) !== null) {
            return;
        }

        $this->createTable(
            self::TN,
            [
                'id' => $this->primaryKey(),

                'number' => $this->integer()->defaultValue(0),

                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
            ]
        );

        $this->createIndex(
            'IDX-main_page_setting',
            self::TN,
            ['number']
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        if ($this->db->getTableSchema(self::TN, true) === null) {
            return;
        }

        $this->dropIndex('IDX-main_page_setting', self::TN);

        $this->dropTable(self::TN);

        Yii::$app->db->schema->refresh();
    }
}
