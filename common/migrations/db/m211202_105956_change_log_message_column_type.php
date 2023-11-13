<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211202_105956_change_log_message_column_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = $this->getDb()->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT');
        if (Yii::$app->db->driverName === 'pgsql') {
            $text = $this->text();
        }
        $this->alterColumn('{{%system_log}}', 'message', $text);
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        if (Yii::$app->db->driverName === 'mysql') {
            $this->alterColumn('{{%system_log}}', 'message', $this->text());
        }
        Yii::$app->db->schema->refresh();
    }
}
