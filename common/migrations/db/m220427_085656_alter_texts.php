<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220427_085656_alter_texts extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        \common\models\settings\CodeSetting::updateAll([
            'description' => 'Код категории приема поступающих имеющих особое право',
        ], [
            'name' => 'category_specific_law',
        ]);
        \common\models\settings\TextSetting::updateAll(
            [
                'description' => 'Сообщение поступающему о том, что анкета была создана из 1С',
                'tooltip_description' => '"Появляется в анкете поступающего. Текст отображается в том случае, если пользователь восстановил анкету из 1С:Университет ПРОФ и ещё не подал заявление на проверку.
Отображается в виде сообщения типа "Успех" (текст на зеленом фоне)."',
            ],
            [
                'name' => 'questionary__create_from_1C',
            ]
        );
        \common\models\settings\TextSetting::updateAll(
            [
                'tooltip_description' => 'Статус используется для заявлений, которые находятся на проверке у модератора (поступающий отправил заявление, модератор его открыл для проверки). Статус используется при включенной опции "Сохранять изменения внесённые модератором в заявление поданное поступающим".',
            ],
            [
                'name' => 'draft_status_application_moderating',
            ]
        );
        \common\models\settings\TextSetting::updateAll(
            [
                'tooltip_description' => 'Статус используется для заявлений, которые находятся на проверке у модератора (поступающий отправил заявление, модератор его открыл для проверки). Статус используется при включенной опции "Сохранять изменения внесённые модератором в заявление поданное поступающим".',
            ],
            [
                'name' => 'draft_status_application_moderating',
            ]
        );
    }

    


    public function safeDown()
    {

    }
}
