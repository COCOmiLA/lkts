<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160414_133030_add_department_code_to_passport extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%passport_data}}', 'department_code', $this->string('50')->notNull());

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%passport_data}}', 'department_code');

        Yii::$app->db->schema->refresh();
    }
}
