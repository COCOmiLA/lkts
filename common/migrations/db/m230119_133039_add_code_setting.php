<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230119_133039_add_code_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%code_settings}}';

    


    public function safeUp()
    {
        $this->insert(self::TN, [
            'name' => 'not_verified_status_document_checker',
            'description' => 'Значение статуса «Не проверен» для проверяемых документов (в ЕПГУ, ФДРО, ...)',
            'value' => '',
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(self::TN, ['name' => ['not_verified_status_document_checker']]);

        Yii::$app->db->schema->refresh();
    }
}
