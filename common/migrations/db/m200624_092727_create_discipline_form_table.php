<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200624_092727_create_discipline_form_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->createTable('{{%dictionary_discipline_form}}', [
            'id' => $this->primaryKey(),
            'discipline_form_name' => $this->string()->null(),
            'discipline_form_id' => $this->string()->null(),
            'archive' => $this->boolean(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ]);
    }

    


    public function down()
    {
        $this->dropTable('{{%dictionary_discipline_form}}');
    }
}
