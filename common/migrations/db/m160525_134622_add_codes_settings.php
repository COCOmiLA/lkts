<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160525_134622_add_codes_settings extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%code_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),
            
        ], $tableOptions);
              
        $this->insert('{{%code_settings}}', [
            'name' => 'citizenship_code',
            'description' => 'Код гражданства по умолчанию (РФ)',
            'value' => '000000001',
        ]);
        
        $this->insert('{{%code_settings}}', [
            'name' => 'identity_docs_code',
            'description' => 'Код категории "Документы, удостоверяющие личность (паспорта)"',
            'value' => '000000001',
        ]);
                
        $this->insert('{{%code_settings}}', [
            'name' => 'passport_code',
            'description' => 'Код документа, удостоверяющего личность по умолчанию (Паспорт РФ)',
            'value' => '000000047',
        ]);
                        
        $this->insert('{{%code_settings}}', [
            'name' => 'edu_docs_code',
            'description' => 'Код категории "Документы об образовании"',
            'value' => '000000018',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->dropTable('{{%code_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
