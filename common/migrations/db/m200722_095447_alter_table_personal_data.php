<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200722_095447_alter_table_personal_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn(
            '{{%personal_data}}',
            'passport_series',
            $this->string(50)->defaultValue(null)
        );
        $this->alterColumn(
            '{{%personal_data}}',
            'passport_number',
            $this->string(50)->defaultValue(null)
        );
    }

    


    public function safeDown()
    {
        $this->update(
            '{{%personal_data}}',
            ['passport_series' => ''],
            ['IS', 'passport_series', null]
        );
        $this->update(
            '{{%personal_data}}',
            ['passport_number' => ''],
            ['IS', 'passport_number', null]
        );

        $this->alterColumn(
            '{{%personal_data}}',
            'passport_series',
            $this->string(50)->notNull()
        );
        $this->alterColumn(
            '{{%personal_data}}',
            'passport_number',
            $this->string(50)->notNull()
        );
    }
}
