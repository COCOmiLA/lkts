<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230307_081940_change_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete(
            '{{%code_settings}}',
            ['name' => 'chosen_campaign_id_by_default']
        );
        $this->insert(
            '{{%code_settings}}',
            [
                'name' => 'chosen_campaign_id_by_default',
                'description' => 'Приёмная кампания, выбранная по умолчанию, в списке, при добавлении новой приёмной кампании',
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
        $this->insert(
            '{{%code_settings}}',
            [
                'name' => 'chosen_campaign_id_by_default',
                'description' => 'Приёмная кампания, выбранная по умолчанию, в списке, при добавлении новой приёмной кампании',
                'value' => '',
            ]
        );
    }
}
