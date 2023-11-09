<?php

use common\components\Migration\SafeMigration;
use yii\db\Query;




class m230523_153455_add_code_settings_for_enrollment_rejection_doc extends SafeMigration
{
    


    public function safeUp()
    {
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'enrollment_rejection_doc_type_guid'])
            ->exists();

        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'enrollment_rejection_doc_type_guid',
                'value' => '',
                'description' => 'Тип документа Отказ от зачисления',
            ]);
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => 'enrollment_rejection_doc_type_guid']);
    }
}
