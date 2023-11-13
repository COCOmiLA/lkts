<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220807_053741_add_allow_remove_sent_application_after_moderation_setting extends MigrationWithDefaultOptions
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
                    'name' => 'allow_remove_sent_application_after_moderation',
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
        $this->delete('{{%application_type_settings}}', ['name' => 'allow_remove_sent_application_after_moderation']);
    }
}
