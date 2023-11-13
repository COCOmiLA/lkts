<?php






echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

?>

use common\components\Migration\SafeMigration;
use common\modules\abiturient\models\bachelor\ApplicationType;
use yii\db\Query;

/**
 * Class <?= $className . "\n" ?>
 */
class <?= $className ?> extends SafeMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->schema->refresh();

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

        $newAppType = new ApplicationType();
        foreach ($applicationsTypes as $applicationsType) {
            $appTypeId = $applicationsType['id'];
            foreach ($applicationsType as $name => $value) {
                if (in_array($name, ApplicationType::LIST_EXCLUSIONS)) {
                    continue;
                }
                if ($name[0] === '_') {
                    $name = substr($name, 1);
                }

                $setting = (new Query())
                    ->from($tableName)
                    ->where([
                        'application_type_id' => $appTypeId,
                        'name' => $name,
                    ])
                    ->one();
                if ($setting) {
                    $this->update(
                        $tableName,
                        [
                            'value' => $value,

                            'updated_at' => time(),
                        ],
                        ['id' => $setting['id']]
                    );
                } else {
                    $this->insert(
                        $tableName,
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
        }

        $connection = Yii::$app->db;
        $columns = $connection->getTableSchema('{{%application_type}}')->getColumnNames();
        foreach ($columns as $name) {
            if (in_array($name, ApplicationType::LIST_EXCLUSIONS)) {
                continue;
            }

            $this->renameColumn('{{%application_type}}', $name, "_{$name}");
        }

        Yii::$app->db->schema->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
