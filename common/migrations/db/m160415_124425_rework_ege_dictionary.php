<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160415_124425_rework_ege_dictionary extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'ref_key');
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'data_version');
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'description');
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'short_name');
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'full_name');
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'parent_key');

        $this->addColumn('{{%dictionary_ege_discipline}}', 'discipline_name', $this->string(1000)->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->addColumn('{{%dictionary_ege_discipline}}', 'ref_key', $this->string(255)->notNull());
        $this->addColumn('{{%dictionary_ege_discipline}}', 'data_version', $this->string(100));
        $this->addColumn('{{%dictionary_ege_discipline}}', 'description', $this->string(1000)->notNull());
        $this->addColumn('{{%dictionary_ege_discipline}}', 'short_name', $this->string(1000));
        $this->addColumn('{{%dictionary_ege_discipline}}', 'full_name', $this->string(1000));
        $this->addColumn('{{%dictionary_ege_discipline}}', 'parent_key', $this->string(255));
        
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'discipline_name');
        Yii::$app->db->schema->refresh();
    }
}
