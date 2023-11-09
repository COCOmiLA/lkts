<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160219_072013_add_1cid_fields extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'id_1c', $this->string(255));
        $this->addColumn('{{%bachelor_speciality}}', 'id_1c', $this->string(255));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'id_1c');
        $this->dropColumn('{{%bachelor_speciality}}', 'id_1c');
        
        Yii::$app->db->schema->refresh();
    }
}
