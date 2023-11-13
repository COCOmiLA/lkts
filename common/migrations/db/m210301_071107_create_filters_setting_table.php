<?php

use backend\models\FiltersSetting;
use common\components\Migration\MigrationWithDefaultOptions;




class m210301_071107_create_filters_setting_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            FiltersSetting::tableName(),
            [
                'id' => $this->primaryKey(),

                'serial' => $this->integer()->defaultValue(null),
                'name' => $this->string()->defaultValue(''),
                'label' => $this->string()->defaultValue(''),
                'description' => $this->string(500)->defaultValue(''),
                'show_column' => $this->boolean()->defaultValue(FiltersSetting::DISABLE),
                'show_filter' => $this->boolean()->defaultValue(FiltersSetting::DISABLE),
            ],
            $tableOptions
        );
        Yii::$app->db->schema->refresh();

        $i = 1;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "statusBlock" в таблице фильтров: `<span class="fa fa-lock"></span>`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'statusBlock',
                'label' => 'Подпись колонки для поля "statusBlock" в таблице фильтров: `<span class="fa fa-lock"></span>`',
                'description' => 'Статус блокировки заявления',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "fio" в таблице фильтров: `ФИО`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'fio',
                'label' => 'Подпись колонки для поля "fio" в таблице фильтров: `ФИО`',
                'description' => 'Фамилия имя отчество поступающего',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "usermail" в таблице фильтров: `Email`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'usermail',
                'label' => 'Подпись колонки для поля "usermail" в таблице фильтров: `Email`',
                'description' => 'Электронная почта поступающего',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "guid" в таблице фильтров: `№ личного дела`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'guid',
                'label' => 'Подпись колонки для поля "guid" в таблице фильтров: `№ личного дела`',
                'description' => 'Номер личного дела',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "campaign_code" в таблице фильтров: `Приёмная кампания`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'campaign_code',
                'label' => 'Подпись колонки для поля "campaign_code" в таблице фильтров: `Приёмная кампания`',
                'description' => 'Приёмная кампания',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "citizenship" в таблице фильтров: `Гражданство`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'citizenship',
                'label' => 'Подпись колонки для поля "citizenship" в таблице фильтров: `Гражданство`',
                'description' => 'Гражданство поступающего',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "hasIndividualAchievement" в таблице фильтров: `Наличие ИД`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'hasIndividualAchievement',
                'label' => 'Подпись колонки для поля "hasIndividualAchievement" в таблице фильтров: `Наличие ИД`',
                'description' => 'Наличие индивидуальных достижений',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "targetReception" в таблице фильтров: `Целевой приём`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'targetReception',
                'label' => 'Подпись колонки для поля "targetReception" в таблице фильтров: `Целевой приём`',
                'description' => 'Целевой приём',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "preferences" в таблице фильтров: `Особые права`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'preferences',
                'label' => 'Подпись колонки для поля "preferences" в таблице фильтров: `Особые права`',
                'description' => 'Особые права',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "specialityName" в таблице фильтров: `Направления`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'specialityName',
                'label' => 'Подпись колонки для поля "specialityName" в таблице фильтров: `Направления`',
                'description' => 'Направления подготовки',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "educationForm" в таблице фильтров: `Форма обучения`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'educationForm',
                'label' => 'Подпись колонки для поля "educationForm" в таблице фильтров: `Форма обучения`',
                'description' => 'Форма обучения',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "status" в таблице фильтров: `Статус`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'status',
                'label' => 'Подпись колонки для поля "status" в таблице фильтров: `Статус`',
                'description' => 'Статус заявления',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "sent_at" в таблице фильтров: `Отправлено`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'sended_at',
                'label' => 'Подпись колонки для поля "sent_at" в таблице фильтров: `Отправлено`',
                'description' => 'Время отправки заявления',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "created_at" в таблице фильтров: `Создано`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'created_at',
                'label' => 'Подпись колонки для поля "created_at" в таблице фильтров: `Создано`',
                'description' => 'Время создания заявления',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "last_management_at" в таблице фильтров: `Последняя проверка`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'last_management_at',
                'label' => 'Подпись колонки для поля "last_management_at" в таблице фильтров: `Последняя проверка`',
                'description' => 'Время последней проверки модератором',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "lastManagerName" в таблице фильтров: `Модератор`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'lastManagerName',
                'label' => 'Подпись колонки для поля "lastManagerName" в таблице фильтров: `Модератор`',
                'description' => 'Последний проверяющий модератор',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
        $i++;
        Yii::t('abiturient/filter-table', 'Подпись колонки для поля "historyChanges" в таблице фильтров: `Изменения`');
        $this->insert(
            FiltersSetting::tableName(),
            [
                'serial' => $i,
                'name' => 'historyChanges',
                'label' => 'Подпись колонки для поля "historyChanges" в таблице фильтров: `Изменения`',
                'description' => 'Последив изменения внесённые в заявление',
                'show_column' => FiltersSetting::ENABLE,
                'show_filter' => FiltersSetting::ENABLE,
            ]
        );
    }

    


    public function safeDown()
    {
        $this->dropTable(FiltersSetting::tableName());
    }
}
