<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220807_055756_add_allow_secondary_apply_after_approval_setting extends MigrationWithDefaultOptions
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
                    'name' => 'allow_secondary_apply_after_approval',
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
        $this->delete('{{%application_type_settings}}', ['name' => 'allow_secondary_apply_after_approval']);
    }
}
