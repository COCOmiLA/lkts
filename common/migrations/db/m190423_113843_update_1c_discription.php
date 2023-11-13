<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190423_113843_update_1c_discription extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->update('{{%text_settings}}', ['value' => 'Получение информации из "1С:Университет ПРОФ" возможно после одобрения заявления модератором'], [
            'name' => 'load_from_1c_info'
        ]);
    }

    


    public function safeDown()
    {
        $this->update('{{%text_settings}}', ['value' => 'Получение информации из 1С возможно после подачи заявления'], [
            'name' => 'load_from_1c_info'
        ]);
    }

    
    public function up()
    {
        $this->update('{{%text_settings}}', ['value' => 'Получение информации из "1С:Университет ПРОФ" возможно после одобрения заявления модератором'], [
            'name' => 'load_from_1c_info'
        ]);
    }

    public function down()
    {
        $this->update('{{%text_settings}}', ['value' => 'Получение информации из 1С возможно после подачи заявления'], [
            'name' => 'load_from_1c_info'
        ]);
    }
}
