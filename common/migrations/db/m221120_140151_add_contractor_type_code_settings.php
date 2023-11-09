<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221120_140151_add_contractor_type_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_ufms_guid'])
            ->exists();
            
            if (!$exists) {
                $this->insert('{{%code_settings}}', [
                    'name' => 'contractor_type_ufms_guid',
                    'description' => 'Тип контрагента по умолчанию "УФМС, ОВД"',
                    'value' => ''
                ]);
            }
            
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_edu_guid'])
            ->exists();

        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'contractor_type_edu_guid',
                'description' => 'Тип контрагента по умолчанию "Образовательные учреждения"',
                'value' => ''
            ]);
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', [
            'name' => ['contractor_type_ufms_guid', 'contractor_type_edu_guid']
        ]);
    }
}
