<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m170328_140859_add_show_list_to_app_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%application_type}}');
        if (!isset($table->columns['show_list'])) {
            $this->addColumn('{{%application_type}}', 'show_list', $this->smallInteger()->defaultValue(0));
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%application_type}}');
        if (isset($table->columns['show_list'])) {
            $this->dropColumn('{{%application_type}}', 'show_list');
        }

        Yii::$app->db->schema->refresh();
    }
}
