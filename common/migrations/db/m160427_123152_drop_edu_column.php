<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160427_123152_drop_edu_column extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%education_data}}', 'education_level_id', $this->integer());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%education_data}}', 'education_level_id', $this->integer()->notNull());
        Yii::$app->db->schema->refresh();
    }
}
