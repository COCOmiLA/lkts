<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160718_083117_add_order_to_application extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}','have_order', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%bachelor_application}}','order_info', $this->string(2000));
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}','have_order');
        $this->dropColumn('{{%bachelor_application}}','order_info');
        
        Yii::$app->db->schema->refresh();
    }
}
