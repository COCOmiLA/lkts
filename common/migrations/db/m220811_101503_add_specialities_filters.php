<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220811_101503_add_specialities_filters extends MigrationWithDefaultOptions
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
                    'name' => 'filter_spec_by_detail_group',
                    'value' => 1,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
            $this->insert(
                '{{%application_type_settings}}',
                [
                    'name' => 'filter_spec_by_special_law',
                    'value' => 1,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%application_type_settings}}', ['name' => 'filter_spec_by_detail_group']);
        $this->delete('{{%application_type_settings}}', ['name' => 'filter_spec_by_special_law']);
    }
}
