<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m150917_083827_addbirthday extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%user_profile}}', 'birthday', $this->string(255));

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user_profile}}', 'birthday');

        Yii::$app->db->schema->refresh();
    }
}
