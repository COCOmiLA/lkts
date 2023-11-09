<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221208_082932_add_contractor_type_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_pref_guid'])
            ->exists();
            
            if (!$exists) {
                $this->insert('{{%code_settings}}', [
                    'name' => 'contractor_type_pref_guid',
                    'description' => 'Тип контрагента по умолчанию "Льготы"',
                    'value' => ''
                ]);
            }
            
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_olymp_guid'])
            ->exists();

        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'contractor_type_olymp_guid',
                'description' => 'Тип контрагента по умолчанию "Олимпиады"',
                'value' => ''
            ]);
        }

        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_target_reception_guid'])
            ->exists();

        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'contractor_type_target_reception_guid',
                'description' => 'Тип контрагента по умолчанию "Целевой прием"',
                'value' => ''
            ]);
        }

        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'contractor_type_ia_guid'])
            ->exists();

        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'contractor_type_ia_guid',
                'description' => 'Тип контрагента по умолчанию "Индивидуальные достижения"',
                'value' => ''
            ]);
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', [
            'name' => [
                'contractor_type_pref_guid',
                'contractor_type_olymp_guid',
                'contractor_type_target_reception_guid',
                'contractor_type_ia_guid'
            ]
        ]);
    }
}
