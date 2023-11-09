<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230301_144805_fix_text_message extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->update('{{%text_settings}}', [
            'value' => 'Серия заполняется только для документов, выданных до 2014 года',
            'default_value' => 'Серия заполняется только для документов, выданных до 2014 года'
        ], [
            'language' => 'ru',
            'name' => 'info_message_in_education_for_series',
        ]);
    }

}
