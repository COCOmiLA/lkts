<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160614_104159_add_russia_and_edu_code_defaults extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'russia_code',
            'description' => 'Код страны по умолчанию (Россия)',
            'value' => '643',
        ]);

        $this->insert('{{%code_settings}}', [
            'name' => 'edu_type_code',
            'description' => 'Код уровня образования по умолчанию',
            'value' => '000000002',
        ]);

        $this->insert('{{%code_settings}}', [
            'name' => 'edu_defaultdoc_code',
            'description' => 'Код типа документа об образовании по умолчанию',
            'value' => '000000026',
        ]);

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->delete('{{%code_settings}}', [
            'name' => ['russia_code', 'edu_type_code', 'edu_defaultdoc_code']
        ]);
    }
}
