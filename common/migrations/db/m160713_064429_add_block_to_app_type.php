<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160713_064429_add_block_to_app_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'blocked', $this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'blocked');
        Yii::$app->db->schema->refresh();
    }
}
