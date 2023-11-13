<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160704_141454_add_parent_id_to_dics extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_ege_discipline}}', 'parent_id', $this->integer()->defaultValue(0));
        $this->addColumn('{{%dictionary_entrance_test_discipline}}', 'parent_id', $this->integer()->defaultValue(0));
        
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'parent_id');
        $this->dropColumn('{{%dictionary_entrance_test_discipline}}', 'parent_id');
        
        Yii::$app->db->schema->refresh();
    }
}
