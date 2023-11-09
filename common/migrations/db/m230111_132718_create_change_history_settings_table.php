<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230111_132718_create_change_history_settings_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%change_history_settings}}';

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),

            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->insert(self::TN, [
            'name' => 'first_load_limit',
            'description' => 'Количество элементов истории загружаемых при инициализации окна',
            'value' => '25',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'name' => 'following_load_limit',
            'description' => 'Количество элементов истории загружаемых во время "бесконечной" прокрутки',
            'value' => '10',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropTable(self::TN);

        Yii::$app->db->schema->refresh();
    }
}
