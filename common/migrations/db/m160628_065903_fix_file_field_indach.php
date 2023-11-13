<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160628_065903_fix_file_field_indach extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%individual_achievement}}','file', $this->string(1000));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%individual_achievement}}','file', $this->string(1000)->notNull());
        Yii::$app->db->schema->refresh();
    }
}
