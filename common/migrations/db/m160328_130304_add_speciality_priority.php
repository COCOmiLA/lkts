<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160328_130304_add_speciality_priority extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'priority', $this->integer()->notNull());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'priority');
        Yii::$app->db->schema->refresh();
    }
}
