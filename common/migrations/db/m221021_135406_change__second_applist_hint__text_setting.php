<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221021_135406_change__second_applist_hint__text_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->update(
            self::TN,
            [
                'value' => 'Выберите направления для поступления (максимум {MAX_COUNT})',
                'default_value' => 'Выберите направления для поступления (максимум {MAX_COUNT})',
            ],
            ['name' => 'second_applist_hint']
        );

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->update(
            self::TN,
            [
                'value' => 'Выберите направления для поступления (максимум 10)',
                'default_value' => 'Выберите направления для поступления (максимум 10)',
            ],
            ['name' => 'second_applist_hint']
        );

        $this->db->schema->refresh();
    }
}
