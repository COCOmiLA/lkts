<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220118_115645_add_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'doc_type_centralized_testing',
            'description' => 'Тип документа "Результат централизованного тестирования Республики Беларусь"',
            'value' => '',
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => ['doc_type_centralized_testing']]);

        Yii::$app->db->schema->refresh();
    }
}
