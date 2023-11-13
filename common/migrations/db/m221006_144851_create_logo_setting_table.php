<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221006_144851_create_logo_setting_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%logo_setting}}';

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),
            'name' => $this->string(100),
            'extension' => $this->string(5),
            'description' => $this->string(100)->comment('Описание'),
            'height' => $this->integer()->defaultValue(0)->comment('Высота'),
            'width' => $this->integer()->defaultValue(0)->comment('Ширина'),
        ]);

        Yii::t('backend', 'Логотип, который будут видеть не авторизованные пользователи');
        $this->insert(self::TN, [
            'name' => 'logo-without-username',
            'description' => 'Логотип, который будут видеть не авторизованные пользователи',
        ]);

        Yii::t('backend', 'Логотип, который будут видеть авторизованные пользователи');
        $this->insert(self::TN, [
            'name' => 'logo-with-username',
            'description' => 'Логотип, который будут видеть авторизованные пользователи',
        ]);

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropTable(self::TN);

        $this->db->schema->refresh();
    }
}
