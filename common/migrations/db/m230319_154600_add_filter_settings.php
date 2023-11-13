<?php

use backend\models\FiltersSetting;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230319_154600_add_filter_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = '{{%filters_setting}}';

        $i = (new Query())
            ->select('MAX(serial)')
            ->from($table)
            ->scalar();

        if (empty($i)) {
            $i = 0;
        }

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "financial_basis" в таблице фильтров: `Форма оплаты`');
        $this->insert(
            $table,
            [
                'serial' => ++$i,
                'name' => 'financial_basis',
                'label' => 'Подпись колонки для поля "financial_basis" в таблице фильтров: `Форма оплаты`',
                'show_column' => true,
                'show_filter' => true,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "education_level" в таблице фильтров: `Уровень образования`');
        $this->insert(
            $table,
            [
                'serial' => ++$i,
                'name' => 'education_level',
                'label' => 'Подпись колонки для поля "education_level" в таблице фильтров: `Уровень образования`',
                'show_column' => true,
                'show_filter' => true,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "admission_category" в таблице фильтров: `Категория приема`');
        $this->insert(
            $table,
            [
                'serial' => ++$i,
                'name' => 'admission_category',
                'label' => 'Подпись колонки для поля "admission_category" в таблице фильтров: `Категория приема`',
                'show_column' => true,
                'show_filter' => true,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "phone_number" в таблице фильтров: `Номер телефона`');
        $this->insert(
            $table,
            [
                'serial' => ++$i,
                'name' => 'phone_number',
                'label' => 'Подпись колонки для поля "phone_number" в таблице фильтров: `Номер телефона`',
                'show_column' => true,
                'show_filter' => true,
            ]
        );

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "snils" в таблице фильтров: `Номер СНИЛС`');
        $this->insert(
            $table,
            [
                'serial' => ++$i,
                'name' => 'snils',
                'label' => 'Подпись колонки для поля "snils" в таблице фильтров: `Номер СНИЛС`',
                'show_column' => true,
                'show_filter' => true,
            ]
        );
    }

    


    public function safeDown()
    {
        $table = '{{%filters_setting}}';
        $this->delete(
            $table,
            ['name' => [
                'financial_basis',
                'education_level',
                'admission_category',
                'phone_number',
                'snils'                
            ]]
        );
    }
}
