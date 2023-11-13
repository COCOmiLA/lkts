<?php

use backend\models\FiltersSetting;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m211123_093959_add_filter_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tn = FiltersSetting::tableName();

        $i = (new Query())
            ->select('MAX(serial)')
            ->from($tn)
            ->scalar();

        if (empty($i)) {
            $i = 0;
        }

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "birthday" в таблице фильтров: `Дата рождения`');
        $this->insert(
            $tn,
            [
                'serial' => ++$i,
                'name' => 'birthday',
                'label' => 'Подпись колонки для поля "birthday" в таблице фильтров: `Дата рождения`',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "exam_form" в таблице фильтров: `Форма испытания`');
        $this->insert(
            $tn,
            [
                'serial' => ++$i,
                'name' => 'exam_form',
                'label' => 'Подпись колонки для поля "exam_form" в таблице фильтров: `Форма испытания`',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "applicantsComment" в таблице фильтров: `Комментарий поступающего`');
        $this->insert(
            $tn,
            [
                'serial' => ++$i,
                'name' => 'applicantsComment',
                'label' => 'Подпись колонки для поля "applicantsComment" в таблице фильтров: `Комментарий поступающего`',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "moderatorsComment " в таблице фильтров: `Комментарий модератора`');
        $this->insert(
            $tn,
            [
                'serial' => ++$i,
                'name' => 'moderatorsComment',
                'label' => 'Подпись колонки для поля "moderatorsComment " в таблице фильтров: `Комментарий модератора`',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
    }

    


    public function safeDown()
    {
        $tn = FiltersSetting::tableName();
        $this->delete(
            $tn,
            ['name' => [
                'birthday',
                'exam_form',
                'applicantsComment',
                'moderatorsComment',
            ]]
        );
    }
}
