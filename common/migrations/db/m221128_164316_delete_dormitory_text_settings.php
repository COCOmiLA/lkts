<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221128_164316_delete_dormitory_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete('{{%text_settings}}', [
            'category' => 'dormitory',
        ]);
    }

    


    public function safeDown()
    {
        return true;
    }
}
