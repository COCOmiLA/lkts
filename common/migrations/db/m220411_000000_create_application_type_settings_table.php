<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220411_000000_create_application_type_settings_table extends MigrationWithDefaultOptions
{
    const LIST_EXCLUSIONS = ['id', 'name', 'archive', 'campaign_id', 'blocked'];

    


    public function safeUp()
    {
        $tableName = '{{%application_type_settings}}';
        if ($this->db->getTableSchema($tableName, true) === null) {
            $this->createTable(
                $tableName,
                [
                    'id' => $this->primaryKey(),

                    'application_type_id' => $this->integer()->defaultValue(null),
                    'name' => $this->string()->defaultValue(null),
                    'value' => $this->boolean()->defaultValue(null),

                    'created_at' => $this->integer(),
                    'updated_at' => $this->integer(),
                ]
            );

            $this->addForeignKey(
                'FK_to_application_type_for_settings',
                $tableName,
                'application_type_id',
                '{{%application_type}}',
                'id'
            );

            $this->createIndex(
                'IDX_for_application_type_settings',
                $tableName,
                'application_type_id'
            );
        }

        $applicationsTypes = (new Query())
            ->from('{{%application_type}}')
            ->all();

        foreach ($applicationsTypes as $applicationsType) {
            $appTypeId = $applicationsType['id'];
            foreach ($applicationsType as $name => $value) {
                if (in_array($name, self::LIST_EXCLUSIONS)) {
                    continue;
                }

                $this->insert(
                    '{{%application_type_settings}}',
                    [
                        'name' => $name,
                        'value' => $value,
                        'application_type_id' => $appTypeId,

                        'created_at' => time(),
                        'updated_at' => time(),
                    ]
                );
            }
        }

        $connection = Yii::$app->db;
        $columns = $connection->getTableSchema('{{%application_type}}')->getColumnNames();
        foreach ($columns as $name) {
            if (in_array($name, self::LIST_EXCLUSIONS)) {
                continue;
            }

            $this->renameColumn('{{%application_type}}', $name, "_{$name}");
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_to_application_type_for_settings', '{{%application_type_settings}}');

        $this->dropIndex('IDX_for_application_type_settings', '{{%application_type_settings}}');

        $this->dropTable('{{%application_type_settings}}');

        $connection = Yii::$app->db;
        $columns = $connection->getTableSchema('{{%application_type}}')->getColumnNames();
        foreach ($columns as $name) {
            if (in_array($name, self::LIST_EXCLUSIONS)) {
                continue;
            }

            $this->renameColumn('{{%application_type}}', $name, trim((string)$name, '_'));
        }

        Yii::$app->db->schema->refresh();
    }
}
