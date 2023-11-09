<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160315_090938_add_kod_zayavleniya_to_spec extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'application_code', $this->string("255"));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'application_code');
        Yii::$app->db->schema->refresh();
    }
}
