<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200819_103835_insert_agreement_decline_text_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

        $this->insert('{{%text_settings}}', [
            'name' => 'info_agreement_decline',
            'category' => 4,
            'description' => 'Сообщение пользователю о порядке прикрепления отзыва согласия на зачисление',
            'value' => 'Для прикрепления отзыва согласия на зачисление необходимо скачать бланк документа, распечатать его, заполнить, подписать, отсканировать и прикрепить обратно.',
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => 'info_agreement_decline'
        ]);
    }

    













}
