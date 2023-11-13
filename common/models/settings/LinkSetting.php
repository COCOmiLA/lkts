<?php

namespace common\models\settings;







class LinkSetting extends Setting
{
    


    public static function tableName()
    {
        return '{{%link_settings}}';
    }
    
    public function rules()
    {
        return [
            [['name', 'description', 'title'], 'required'],
            ['name', 'string', 'max' => 100],
            [['url','description','title'], 'string', 'max' => 1000],
            ['name', 'unique'],
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'description' => 'Описание',
            'title' => 'Текст ссылки',
            'url' => 'адрес',
        ];
    }
    
    public function isActive(){
        if(strlen((string)$this->url) > 0){
            return true;
        }
        return false;
    }
}