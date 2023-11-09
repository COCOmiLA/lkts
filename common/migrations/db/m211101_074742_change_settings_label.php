<?php

use common\components\LikeQueryManager;
use yii\db\Migration;




class m211101_074742_change_settings_label extends Migration
{
    


    public function safeUp()
    {
        $tn = '{{%filters_setting}}';
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "fio" в таблице фильтров: `ФИО`');
        $this->update(
            $tn,
            ['label' => 'Подпись колонки для поля "fio" в таблице фильтров: `ФИО`'],
            [LikeQueryManager::getActionName(), "{$tn}.label", 'ФИО']
        );
    }

    


    public function safeDown()
    {
        $tn = '{{%filters_setting}}';

        $this->update(
            $tn,
            ['label' => 'ФИО'],
            [LikeQueryManager::getActionName(), "{$tn}.label", 'Ф. И. О.']
        );
    }
}
