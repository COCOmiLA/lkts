<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\MaxSpecialityType;
use yii\db\Query;




class m210518_130201_add_max_speciality_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if (!$this->codeExists('max_speciality_type.speciality')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.speciality',
                'description' => 'Тип ограничения количества направлений "По направлениям подготовки"',
                'value' => MaxSpecialityType::TYPE_SPECIALITY
            ]);
        }
        
        if (!$this->codeExists('max_speciality_type.group')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.group',
                'description' => 'Тип ограничения количества направлений "По конкурсным группам"',
                'value' => MaxSpecialityType::TYPE_GROUP
            ]);
        }
        
        if (!$this->codeExists('max_speciality_type.faculty')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.faculty',
                'description' => 'Тип ограничения количества направлений "По факультетам"',
                'value' => MaxSpecialityType::TYPE_FACULTY
            ]);
        }
        
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => [
            'max_speciality_type.speciality',
            'max_speciality_type.group',
            'max_speciality_type.faculty'
        ]]);
        
        \Yii::$app->db->schema->refresh();
    }
    
    protected function codeExists($name): bool
    {
        return (new Query())
            ->select('id')
            ->from('{{%code_settings}}')
            ->where(['name' => $name])
            ->exists();
    }
}
