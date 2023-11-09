<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_155415_add_original_field extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%education_data}}','have_original',$this->smallInteger()->notNull()->defaultValue(0));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%education_data}}','have_original');
        Yii::$app->db->schema->refresh();
    }
}
