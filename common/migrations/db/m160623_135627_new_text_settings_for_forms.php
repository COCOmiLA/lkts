<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160623_135627_new_text_settings_for_forms extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'indach_top_text',
            'description' => 'Текст cверху в списке Индивидуальных достижений',
            'value' => '',
            'category' => 8,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'indach_bottom_text',
            'description' => 'Текст снизу в списке Индивидуальных достижений',
            'value' => '',
            'category' => 8,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_top_text',
            'description' => 'Текст cверху в Анкете',
            'value' => '',
            'category' => 2,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_bottom_text',
            'description' => 'Текст снизу в Анкете',
            'value' => '',
            'category' => 2,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'dormitory_top_text',
            'description' => 'Текст cверху в Общежитиях',
            'value' => '',
            'category' => 6,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'dormitory_bottom_text',
            'description' => 'Текст снизу в Общежитиях',
            'value' => '',
            'category' => 6,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'applications_top_text',
            'description' => 'Текст сверху в Списке заявлений',
            'value' => '',
            'category' => 4,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'applications_bottom_text',
            'description' => 'Текст снизу в Списке заявлений',
            'value' => '',
            'category' => 4,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'index_top_text',
            'description' => 'Текст сверху на Главной',
            'value' => '',
            'category' => 1,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'index_bottom_text',
            'description' => 'Текст снизу на Главной',
            'value' => '',
            'category' => 1,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'spec_top_text',
            'description' => 'Текст сверху в списке Направлений подготовки',
            'value' => '',
            'category' => 4,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'spec_bottom_text',
            'description' => 'Текст снизу в списке Направлений подготовки',
            'value' => '',
            'category' => 4,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'education_top_text',
            'description' => 'Текст сверху в Образовании',
            'value' => '',
            'category' => 5,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'education_bottom_text',
            'description' => 'Текст снизу в Образовании',
            'value' => '',
            'category' => 5,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'exam_top_text',
            'description' => 'Текст сверху в Экзаменах',
            'value' => '',
            'category' => 3,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'exam_bottom_text',
            'description' => 'Текст снизу в Экзаменах',
            'value' => '',
            'category' => 3,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'access_top_text',
            'description' => 'Текст сверху на странице создания пароля',
            'value' => '',
            'category' => 0,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'access_bottom_text',
            'description' => 'Текст снизу на странице создания пароля',
            'value' => '',
            'category' => 0,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'login_top_text',
            'description' => 'Текст сверху на странице входа',
            'value' => '',
            'category' => 0,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'login_bottom_text',
            'description' => 'Текст снизу на странице входа',
            'value' => '',
            'category' => 0,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'register_top_text',
            'description' => 'Текст сверху на странице регистрации',
            'value' => '',
            'category' => 0,
        ]);
        $this->insert('{{%text_settings}}', [
            'name' => 'register_bottom_text',
            'description' => 'Текст снизу на странице регистрации',
            'value' => '',
            'category' => 0,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%text_settings}}',[
            'name' => [
                'indach_top_text',
                'indach_bottom_text',
                'questionary_top_text',
                'questionary_bottom_text',
                'dormitory_top_text',
                'dormitory_bottom_text',
                'applications_top_text',
                'applications_bottom_text',
                'index_top_text',
                'index_bottom_text',
                'spec_top_text',
                'spec_bottom_text',
                'education_top_text',
                'education_bottom_text',
                'exam_top_text',
                'exam_bottom_text',
                'access_top_text',
                'access_bottom_text',
                'login_top_text',
                'login_bottom_text',
                'register_top_text',
                'register_bottom_text',
            ]
        ]);
    }
}
