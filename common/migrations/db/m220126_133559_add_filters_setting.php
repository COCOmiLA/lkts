<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220126_133559_add_filters_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table = '{{%filters_setting}}';

        $serial = (new Query())
            ->select('MAX(serial)')
            ->from($table)
            ->scalar();

        if (empty($serial)) {
            $serial = 0;
        }

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "agreement" в таблице фильтров: `Наличие согласия на зачисления`');
        $this->insert($table, [
            'serial' => ++$serial,
            'name' => 'agreement',
            'label' => 'Подпись колонки для поля "agreement" в таблице фильтров: `Наличие согласия на зачисления`',
            'show_column' => true,
            'show_filter' => true,
        ]);

        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "review_agreement" в таблице фильтров: `Наличие отзыва согласия на зачисления`');
        $this->insert($table, [
            'serial' => ++$serial,
            'name' => 'review_agreement',
            'label' => 'Подпись колонки для поля "review_agreement" в таблице фильтров: `Наличие отзыва согласия на зачисления`',
            'show_column' => true,
            'show_filter' => true,
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%filters_setting}}', ['name' => [
            'agreement',
            'review_agreement',
        ]]);
    }
}
