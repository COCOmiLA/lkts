<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220729_143159_add_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%code_settings}}',
            [
                'name' => 'chosen_campaign_id_by_default',
                'description' => 'Приёмная компания, выбранная по умолчанию, в списке, при добавлении новой приёмной компании',
                'value' => '',
            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%code_settings}}',
            ['name' => 'chosen_campaign_id_by_default']
        );
    }
}
