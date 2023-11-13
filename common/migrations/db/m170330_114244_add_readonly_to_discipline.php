<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m170330_114244_add_readonly_to_discipline extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_allowed_forms}}', 'readonly', $this->smallInteger());
        $this->addColumn('{{%bachelor_egeresult}}', 'readonly', $this->smallInteger());
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_allowed_forms}}', 'readonly');
        $this->dropColumn('{{%bachelor_egeresult}}', 'readonly');
        
        Yii::$app->db->schema->refresh();
    }
}
