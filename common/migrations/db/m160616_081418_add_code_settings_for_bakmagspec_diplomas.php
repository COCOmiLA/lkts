<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160616_081418_add_code_settings_for_bakmagspec_diplomas extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'bak_doc_code',
            'description' => 'Код диплома бакалавра',
            'value' => '000000022',
        ]);
        
        $this->insert('{{%code_settings}}', [
            'name' => 'mag_doc_code',
            'description' => 'Код диплома магистра',
            'value' => '000000023',
        ]);
        
        $this->insert('{{%code_settings}}', [
            'name' => 'spec_doc_code',
            'description' => 'Код диплома специалиста',
            'value' => '000000024',
        ]);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->delete('{{%code_settings}}', [
            'name' => [
                'bak_doc_code',
                'mag_doc_code',
                'spec_doc_code',
            ]
        ]);
        
        Yii::$app->db->schema->refresh();
    }
}
