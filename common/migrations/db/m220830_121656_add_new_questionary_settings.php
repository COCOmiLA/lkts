<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m220830_121656_add_new_questionary_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $old_code_setting = (new Query())
            ->select('value')
            ->from('{{%code_settings}}')
            ->where(['name' => 'required_birth_place'])
            ->one();
        
        if (!$this->questionarySettingExists('require_birth_place_abiturient')) {
            $this->insert('{{%questionary_settings}}', [
                'name' => 'require_birth_place_abiturient',
                'description' => 'Требовать обязательное заполнение поля "Место рождения" поступающим',
                'value' => $old_code_setting['value'] ?? 0,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }
        
        if (!$this->questionarySettingExists('require_ctitizenship_abiturient')) {
            $this->insert('{{%questionary_settings}}', [
                'name' => 'require_ctitizenship_abiturient',
                'description' => 'Требовать обязательное заполнение поля "Гражданство" поступающим',
                'value' => 0,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }
        
        if (!$this->questionarySettingExists('require_birth_place_parent')) {
            $this->insert('{{%questionary_settings}}', [
                'name' => 'require_birth_place_parent',
                'description' => 'Требовать обязательное заполнение поля "Место рождения" в данных родителей или законных представителей',
                'value' => 0,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }
        
        if (!$this->questionarySettingExists('require_ctitizenship_parent')) {
            $this->insert('{{%questionary_settings}}', [
                'name' => 'require_ctitizenship_parent',
                'description' => 'Требовать обязательное заполнение поля "Гражданство" в данных родителей или законных представителей',
                'value' => 0,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }
        
        $this->delete('{{%code_settings}}', ['name' => 'required_birth_place']);
    }
    
    


    public function safeDown()
    {
        $exists = (new Query())
            ->from('{{%code_settings}}')
            ->where(['name' => 'required_birth_place'])
            ->exists();
        
        if (!$exists) {
            $this->insert('{{%code_settings}}', [
                'name' => 'required_birth_place',
                'description' => 'Требовать обязательное заполнение поля "Место рождения" поступающим.',
                'value' => 0
            ]);
        }
        
        $this->delete('{{%questionary_settings}}', ['name' => [
            'require_birth_place_abiturient',
            'require_ctitizenship_abiturient',
            'require_birth_place_parent',
            'require_ctitizenship_parent',
        ]]);
    }
    
    protected function questionarySettingExists($name): bool
    {
        return (new Query())->from('{{%questionary_settings}}')->where(['name' => $name])->exists();
    }
}
