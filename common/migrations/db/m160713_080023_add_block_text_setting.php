<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160713_080023_add_block_text_setting extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'block_top_text',
            'description' => 'Текст сверху в заявлении при запрете работы по ПК',
            'value' => 'ВНИМАНИЕ!!! Может быть ограничена подача заявлений в соответствии с регламентом работы приемной комиссии.',
            'category' => 0,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => ['block_top_text']
        ]);
    }
}
