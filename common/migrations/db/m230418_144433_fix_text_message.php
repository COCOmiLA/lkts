<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230418_144433_fix_text_message extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->update('{{%text_settings}}',
            [
                'value' => 'Вы не можете отправить новое заявление на проверку, пока приёмная кампания не примет решение по уже отправленному заявлению.',
                'default_value' => 'Вы не можете отправить новое заявление на проверку, пока приёмная кампания не примет решение по уже отправленному заявлению.',
            ],
            ['name' => 'text_on_disable_creating_draft_if_exist_sent_application']);
    }
}
