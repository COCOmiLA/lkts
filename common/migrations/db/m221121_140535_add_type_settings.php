<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221121_140535_add_type_settings extends MigrationWithDefaultOptions
{
    


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
                    'name' => 'hide_olympic_block',
                    'value' => 0,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
            $this->insert(
                '{{%application_type_settings}}',
                [
                    'name' => 'hide_targets_block',
                    'value' => 0,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%application_type_settings}}', ['name' => 'hide_olympic_block']);
        $this->delete('{{%application_type_settings}}', ['name' => 'hide_targets_block']);
    }
}
