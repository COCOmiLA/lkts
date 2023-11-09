<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221027_122100_create_tag_dictionary_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%tag_dictionary}}';

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),

            'tag' => $this->string(50)->defaultValue(null),
            'role' => $this->string(50)->defaultValue('abiturient'),
            'url_template' => $this->string()->defaultValue(null),
            'description' => $this->string(500)->defaultValue(null),
            'default_alias' => $this->string(50)->defaultValue(null),
            'icon' => $this->string(100)->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->db->schema->refresh();

        
        $this->insert(self::TN, [
            'tag' => '#анкета_основные_данные',
            'description' => 'Анкета блок "Основные данные"',
            'default_alias' => '«Основные данные»',
            'icon' => 'fa fa-user-circle-o',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_basic_data_panel',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_паспортные_данные',
            'description' => 'Анкета блок "Паспортные данные"',
            'default_alias' => '«Паспортные данные»',
            'icon' => 'fa fa-address-book',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_passport_data_panel',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_адрес_постоянной_регистрации',
            'description' => 'Анкета блок "Адрес постоянной регистрации"',
            'default_alias' => '«Адрес постоянной регистрации»',
            'icon' => 'fa fa-map',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_permanent_registration_address',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_адрес_проживания',
            'description' => 'Анкета блок "Адрес проживания"',
            'default_alias' => '«Адрес проживания»',
            'icon' => 'fa fa-map-o',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_address_residence',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_родители',
            'description' => 'Анкета блок "Данные родителей или законных представителей"',
            'default_alias' => '«Данные родителей или законных представителей»',
            'icon' => 'fa fa-users',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_parents',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_прочее',
            'description' => 'Анкета блок "Прочее"',
            'default_alias' => '«Прочее»',
            'icon' => 'fa fa-cubes',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_other_data',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#анкета_документы',
            'description' => 'Анкета блок "Скан-копии документов"',
            'default_alias' => '«Скан-копии документов»',
            'icon' => 'fa fa-book',
            'url_template' => json_encode([
                'abiturient/questionary',
                'app_id' => '{{ID}}',
                '#' => 'questionary_docs',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_сведения_об_образовании',
            'description' => 'Сведения об образовании',
            'default_alias' => '«Сведения об образовании»',
            'icon' => 'fa fa-graduation-cap',
            'url_template' => json_encode([
                'bachelor/education',
                'id' => '{{ID}}'
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_льготы',
            'description' => 'Льготы',
            'default_alias' => '«Льготы»',
            'icon' => 'fa fa-wheelchair-alt',
            'url_template' => json_encode([
                'bachelor/accounting-benefits',
                'id' => '{{ID}}',
                '#' => 'bachelor_benefits',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#заявление_целевые_договоры',
            'description' => 'Целевые договоры',
            'default_alias' => '«Целевые договоры»',
            'icon' => 'fa fa-briefcase',
            'url_template' => json_encode([
                'bachelor/accounting-benefits',
                'id' => '{{ID}}',
                '#' => 'bachelor_target_reception',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#заявление_без_вступительных_испытаний',
            'description' => 'Поступление без вступительных испытаний',
            'default_alias' => '«Поступление без вступительных испытаний»',
            'icon' => 'fa fa-trophy',
            'url_template' => json_encode([
                'bachelor/accounting-benefits',
                'id' => '{{ID}}',
                '#' => 'bachelor_olympiad',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_добавленные_направления',
            'description' => 'Добавленные направления подготовки',
            'default_alias' => '«Добавленные направления подготовки»',
            'icon' => 'fa fa-university',
            'url_template' => json_encode([
                'bachelor/application',
                'id' => '{{ID}}',
                '#' => 'bachelor_added_areas_of_study',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_наборы_вступительных_испытаний',
            'description' => 'Наборы вступительных испытаний',
            'default_alias' => '«Наборы вступительных испытаний»',
            'icon' => 'fa fa-puzzle-piece',
            'url_template' => json_encode([
                'bachelor/ege',
                'id' => '{{ID}}',
                '#' => 'bachelor_entrance_test_sets',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'tag' => '#заявление_результаты_вступительных_испытаний',
            'description' => 'Результаты вступительных испытаний',
            'default_alias' => '«Результаты вступительных испытаний»',
            'icon' => 'fa fa-bar-chart',
            'url_template' => json_encode([
                'bachelor/ege',
                'id' => '{{ID}}',
                '#' => 'bachelor_entrance_test_results',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_индивидуальные_достижения',
            'description' => 'Индивидуальные достижения',
            'default_alias' => '«Индивидуальные достижения»',
            'icon' => 'fa fa-user-plus',
            'url_template' => json_encode([
                'abiturient/ialist',
                'id' => '{{ID}}',
                '#' => 'bachelor_individual_achievements',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_сканы_документов',
            'description' => 'Сканы документов',
            'default_alias' => '«Сканы документов»',
            'icon' => 'fa fa-archive',
            'url_template' => json_encode([
                'bachelor/load-scans',
                'id' => '{{ID}}',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'tag' => '#заявление_комментарий',
            'description' => 'Комментарий',
            'default_alias' => '«Комментарий»',
            'icon' => 'fa fa-commenting-o',
            'url_template' => json_encode([
                'bachelor/comment',
                'id' => '{{ID}}',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
    }

    


    public function safeDown()
    {
        $this->dropTable(self::TN);

        $this->db->schema->refresh();
    }
}
