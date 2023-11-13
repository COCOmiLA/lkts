<?php

use yii\db\Migration;




class m211109_104700_add_language_and_app_type_to_texts extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%text_settings}}', 'application_type', $this->integer()->defaultValue(0));
        $this->addColumn('{{%text_settings}}', 'language', $this->string()->defaultValue('ru'));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%text_settings}}', 'application_type');
        $this->dropColumn('{{%text_settings}}', 'language');

        Yii::$app->db->schema->refresh();
    }

}
