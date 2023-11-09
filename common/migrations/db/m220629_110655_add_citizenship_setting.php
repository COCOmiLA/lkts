<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220629_110655_add_citizenship_setting extends MigrationWithDefaultOptions
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
                    'name' => 'citizenship_is_required',
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
        $this->delete('{{%application_type_settings}}', ['name' => 'citizenship_is_required']);
    }
}
