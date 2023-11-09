<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160617_103325_add_hide_ege_and_disable_apptype extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%application_type}}','hide_ege',$this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%application_type}}','disable_type',$this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}','hide_ege');
        $this->dropColumn('{{%application_type}}','disable_type');
        
        Yii::$app->db->schema->refresh();
    }
}
