<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220411_000002_add_admission_campaign_options extends MigrationWithDefaultOptions
{
    protected static $new_settings = [
        [
            'name' => 'filter_spec_by_spec',
            'value' => 1,
        ],
        [
            'name' => 'filter_spec_by_code',
            'value' => 1,
        ],
        [
            'name' => 'filter_spec_by_dep',
            'value' => 1,
        ],
        [
            'name' => 'filter_spec_by_eduf',
            'value' => 1,
        ],
        [
            'name' => 'filter_spec_by_fin',
            'value' => 1,
        ],
    ];

    


    public function safeUp()
    {
        $applicationsTypes = (new Query())
            ->select(['id'])
            ->from('{{%application_type}}')
            ->all();

        foreach ($applicationsTypes as $applicationsType) {
            $appTypeId = $applicationsType['id'];

            foreach (static::$new_settings as $setting) {
                $exists = (new Query())->select('id')->from('{{%application_type_settings}}')->where([
                    'name' => $setting['name'],
                    'application_type_id' => $appTypeId
                ])->exists();

                if ($exists) {
                    continue;
                }

                $setting['application_type_id'] = $appTypeId;
                $setting['created_at'] = time();
                $setting['updated_at'] = time();
                $this->insert(
                    '{{%application_type_settings}}',
                    $setting
                );
            }
        }
    }

    


    public function safeDown()
    {
        foreach (static::$new_settings as $setting) {
            $this->delete('{{%application_type_settings}}', [
                'name' => $setting['name']
            ]);
        }
    }
}
