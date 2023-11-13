<?php






echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

?>

use common\components\Migration\SafeMigration;
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
        $applicationsTypes = (new Query())
            ->select(['id'])
            ->from('{{%application_type}}')
            ->all();

        foreach ($applicationsTypes as $applicationsType) {
            $appTypeId = $applicationsType['id'];

            $this->insert(
                '{{%application_type_settings}}',
                [
                    'name' => 'new_setting_name',
                    'value' => 0,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%application_type_settings}}', ['name' => 'new_setting_name']);
    }
}
