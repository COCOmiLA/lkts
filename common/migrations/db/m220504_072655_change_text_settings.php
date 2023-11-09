<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220504_072655_change_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->update(
            '{{%text_settings}}',
            ['category' => 13],
            ['name' => 'applications_top_text']
        );

        $this->update(
            '{{%text_settings}}',
            ['category' => 13],
            ['name' => 'applications_bottom_text']
        );
    }

    


    public function safeDown()
    {
        $this->update(
            '{{%text_settings}}',
            ['category' => 4],
            ['name' => 'applications_top_text']
        );

        $this->update(
            '{{%text_settings}}',
            ['category' => 4],
            ['name' => 'applications_bottom_text']
        );
    }
}
