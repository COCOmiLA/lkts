<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220606_062005_delete_extra_text_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        if (self::hasTextSetting()) {
            $this->delete(self::TN, ['name' => 'reset_link_text']);
        }
    }

    


    public function safeDown()
    {
        if (!self::hasTextSetting()) {
            $this->insert(
                self::TN,
                [
                    'name' => 'reset_link_text',
                    'description' => 'Тексты ссылки на сброс пароля на странице авторизации',
                    'value' => 'Забыли пароль? Перейдите по ссылке',
                    'order' => 0,
                    'category' => 0,
                ]
            );
        }
    }

    private function hasTextSetting(): bool
    {
        return (new Query)
            ->from(self::TN)
            ->where(['name' => 'reset_link_text'])
            ->exists();
    }
}
