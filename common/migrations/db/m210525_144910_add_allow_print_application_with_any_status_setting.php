<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210525_144910_add_allow_print_application_with_any_status_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'allow_print_application_with_any_status',
            'description' => 'Разрешать печать заявления в любом статусе заявления',
            'value' => '0'
        ]);
        
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => [
            'allow_print_application_with_any_status'
        ]]);
        
        \Yii::$app->db->schema->refresh();
    }
}
