<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220119_082919_update_filter_names extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tn = \backend\models\FiltersSetting::tableName();
        $this->update(
            $tn,
            ['label' => 'Подпись колонки для поля "fio" в таблице фильтров: `ФИО`'],
            ['name' => 'fio']
        );
        $this->update(
            $tn,
            ['label' => 'Подпись колонки для поля "campaign_code" в таблице фильтров: `Приёмная кампания`'],
            ['name' => 'campaign_code']
        );
    }

    


    public function safeDown()
    {
        $tn = \backend\models\FiltersSetting::tableName();
        $this->update(
            $tn,
            ['label' => 'Подпись колонки для поля "fio" в таблице фильтров: `Ф. И. О.`'],
            ['name' => 'fio']
        );
        $this->update(
            $tn,
            ['label' => 'Подпись колонки для поля "campaign_code" в таблице фильтров: `Приёмная кампания`'],
            ['name' => 'campaign_code']
        );
    }
}
