<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160401_121044_add_demo_attachment_types extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
         $this->insert('{{%attachment_type}}', [
            'name' => 'Первая страница паспорта',
            'required' => true,
            'related_entity' => 'questionary',
            'created_at' => time(),
            'updated_at' => time()
        ]);
         
        $this->insert('{{%attachment_type}}', [
            'name' => 'Страница сведений о регистрации',
            'required' => false,
            'related_entity' => 'questionary',
            'created_at' => time(),
            'updated_at' => time()
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%attachment_type}}', [
            'name' => [
                'Первая страница паспорта',
                'Страница сведений о регистрации',
            ]
        ]);
    }
}
