<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160721_111203_add_field_need_exams extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'need_exams', $this->smallInteger()->defaultValue(0));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}', 'need_exams');
        Yii::$app->db->schema->refresh();
        return true;
    }
}
