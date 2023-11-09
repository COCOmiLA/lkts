<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160708_141252_add_blocker_id_to_app extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}','blocker_id', $this->integer());
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}','blocker_id');
        
        Yii::$app->db->schema->refresh();
    }
}
