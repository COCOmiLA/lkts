<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230208_153659_rename_code_settings extends MigrationWithDefaultOptions
{
    protected static $to_rename = [
        'target_reception_document_type' => 'target_reception_document_type_guid',
        'doc_type_centralized_testing' => 'centralized_testing_doc_type_guid',
        'belarusian_citizenship_code' => 'belarusian_citizenship_guid',
    ];

    


    public function safeUp()
    {
        foreach (self::$to_rename as $old_name => $new_name) {
            $this->update('{{%code_settings}}', ['name' => $new_name], ['name' => $old_name]);
        }
    }

    


    public function safeDown()
    {
        foreach (self::$to_rename as $old_name => $new_name) {
            $this->update('{{%code_settings}}', ['name' => $old_name], ['name' => $new_name]);
        }
    }
}
