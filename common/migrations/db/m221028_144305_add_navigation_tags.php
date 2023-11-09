<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221028_144305_add_navigation_tags extends MigrationWithDefaultOptions
{
    private const TN = '{{%tag_dictionary}}';

    


    public function safeUp()
    {
        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_основные_данные',
            'description' => 'Анкета блок "Основные данные"',
            'default_alias' => '«Основные данные»',
            'icon' => 'fa fa-user-circle-o',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_basic_data_panel',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_паспортные_данные',
            'description' => 'Анкета блок "Паспортные данные"',
            'default_alias' => '«Паспортные данные»',
            'icon' => 'fa fa-address-book',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_passport_data_panel',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_адрес_постоянной_регистрации',
            'description' => 'Анкета блок "Адрес постоянной регистрации"',
            'default_alias' => '«Адрес постоянной регистрации»',
            'icon' => 'fa fa-map',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_permanent_registration_address',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_адрес_проживания',
            'description' => 'Анкета блок "Адрес проживания"',
            'default_alias' => '«Адрес проживания»',
            'icon' => 'fa fa-map-o',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_address_residence',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_родители',
            'description' => 'Анкета блок "Данные родителей или законных представителей"',
            'default_alias' => '«Данные родителей или законных представителей»',
            'icon' => 'fa fa-users',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_parents',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#анкета_прочее',
            'description' => 'Анкета блок "Прочее"',
            'default_alias' => '«Прочее»',
            'icon' => 'fa fa-cubes',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'questionary_other_data',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_сведения_об_образовании',
            'description' => 'Сведения об образовании',
            'default_alias' => '«Сведения об образовании»',
            'icon' => 'fa fa-graduation-cap',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_education_data',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_льготы',
            'description' => 'Льготы',
            'default_alias' => '«Льготы»',
            'icon' => 'fa fa-wheelchair-alt',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_benefits',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_целевые_договоры',
            'description' => 'Целевые договоры',
            'default_alias' => '«Целевые договоры»',
            'icon' => 'fa fa-briefcase',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_target_reception',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_без_вступительных_испытаний',
            'description' => 'Поступление без вступительных испытаний',
            'default_alias' => '«Поступление без вступительных испытаний»',
            'icon' => 'fa fa-trophy',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_olympiad',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_добавленные_направления',
            'description' => 'Добавленные направления подготовки',
            'default_alias' => '«Добавленные направления подготовки»',
            'icon' => 'fa fa-university',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_added_areas_of_study',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_наборы_вступительных_испытаний',
            'description' => 'Наборы вступительных испытаний',
            'default_alias' => '«Наборы вступительных испытаний»',
            'icon' => 'fa fa-puzzle-piece',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_entrance_test_sets',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_результаты_вступительных_испытаний',
            'description' => 'Результаты вступительных испытаний',
            'default_alias' => '«Результаты вступительных испытаний»',
            'icon' => 'fa fa-bar-chart',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_entrance_test_results',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_индивидуальные_достижения',
            'description' => 'Индивидуальные достижения',
            'default_alias' => '«Индивидуальные достижения»',
            'icon' => 'fa fa-user-plus',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'bachelor_individual_achievements',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_сканы_документов',
            'description' => 'Сканы документов',
            'default_alias' => '«Сканы документов»',
            'icon' => 'fa fa-archive',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'copies_documents',
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        

        
        $this->insert(self::TN, [
            'role' => 'moderator',
            'tag' => '#заявление_комментарий',
            'description' => 'Комментарий',
            'default_alias' => '«Комментарий»',
            'icon' => 'fa fa-commenting-o',
            'url_template' => json_encode([
                'sandbox/moderate',
                'id' => '{{ID}}',
                '#' => 'comment_history'
            ]),

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
    }

    


    public function safeDown()
    {
        $this->delete(
            self::TN,
            [
                'AND',
                ['role' => 'moderator'],
                ['IN', 'tag', [
                    '#анкета_прочее',
                    '#анкета_родители',
                    '#заявление_льготы',
                    '#заявление_комментарий',
                    '#анкета_основные_данные',
                    '#анкета_адрес_проживания',
                    '#анкета_паспортные_данные',
                    '#заявление_сканы_документов',
                    '#заявление_целевые_договоры',
                    '#заявление_добавленные_направления',
                    '#заявление_сведения_об_образовании',
                    '#анкета_адрес_постоянной_регистрации',
                    '#заявление_индивидуальные_достижения',
                    '#заявление_без_вступительных_испытаний',
                    '#заявление_наборы_вступительных_испытаний',
                    '#заявление_результаты_вступительных_испытаний',
                ]],
            ]
        );
    }
}
