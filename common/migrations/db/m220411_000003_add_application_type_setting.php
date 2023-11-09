<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220411_000003_add_application_type_setting extends MigrationWithDefaultOptions
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
                    'name' => 'allow_add_new_education_after_approve',
                    'value' => 0,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );

            $this->insert(
                '{{%application_type_settings}}',
                [
                    'name' => 'allow_add_new_file_to_education_after_approve',
                    'value' => 0,
                    'application_type_id' => $appTypeId,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );

            $this->insert(
                '{{%application_type_settings}}',
                [
                    'name' => 'allow_delete_file_from_education_after_approve',
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
        $this->delete(
            '{{%application_type_settings}}',
            ['name' => [
                'allow_add_new_file_to_education_after_approve',
                'allow_delete_file_from_education_after_approve',
            ]]
        );
    }
}
