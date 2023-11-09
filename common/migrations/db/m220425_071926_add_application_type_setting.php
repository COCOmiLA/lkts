<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220425_071926_add_application_type_setting extends MigrationWithDefaultOptions
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
                    'name' => 'enable_autofill_specialty_on_a_universal_basis',
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
        $this->delete('{{%application_type_settings}}', ['name' => 'enable_autofill_specialty_on_a_universal_basis']);
    }
}
