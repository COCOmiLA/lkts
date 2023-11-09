<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211213_163216_remove_extension_column extends MigrationWithDefaultOptions
{
    protected $tables = [
        '{{%agreement_decline}}',
        '{{%attachment_archive}}',
        '{{%attachment}}',
        '{{%consent}}',
        '{{%document_template}}',
        '{{%admission_agreement}}',
        '{{%individual_achievement}}',
        '{{%bachelor_preferences}}',
        '{{%bachelor_target_reception}}',
    ];

    


    public function safeUp()
    {
        foreach ($this->tables as $table) {
            $table_schema = Yii::$app->db->schema->getTableSchema($table);
            if (isset($table_schema->columns['extension'])) {
                $this->dropColumn($table, 'extension');
            }
        }
    }

    


    public function safeDown()
    {
        foreach ($this->tables as $table) {
            $table_schema = Yii::$app->db->schema->getTableSchema($table);
            if (!isset($table_schema->columns['extension'])) {
                $this->addColumn($table, 'extension', $this->string());
            }
        }
    }
}
