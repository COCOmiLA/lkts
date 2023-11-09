<?php

use yii\db\Migration;




class m211005_073611_add_pk_setting_archive_actual_app extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'archive_actual_app_on_update', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'archive_actual_app_on_update');

        Yii::$app->db->schema->refresh();
    }

}
