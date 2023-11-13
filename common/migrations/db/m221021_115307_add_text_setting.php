<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221021_115307_add_text_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_on_disable_creating_draft_if_exist_sent_application',
                'description' => 'Текст сообщения на всех страницах заявления, когда поступающему запрещено создавать черновики',
                'value' => 'Вы не можете отправить новое заявление на проверку, пока приёмная коми не примет решение по уже отправленному заявлению.',
                'category' => 4,
                'tooltip_description' => 'Текст появляется когда для конкретной приёмной компании включена опция «Запретить поступающему создавать черновик, если есть отправленное на проверку заявление». Отображается в виде жёлтого предупреждающего сообщения.',
                'default_value' => 'Вы не можете отправить новое заявление на проверку, пока приёмная коми не примет решение по уже отправленному заявлению.',
            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', ['name' => 'text_on_disable_creating_draft_if_exist_sent_application']);
    }
}
