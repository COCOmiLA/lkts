<?php

namespace common\models\dictionary;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

















class Fias extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%dictionary_fias}}';
    }

    


    public function behaviors()
    {
        return [[
            'class' => TimestampBehavior::class,
            'updatedAtAttribute' => false
        ]];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'code',
                    'name',
                    'area_code',
                    'city_code',
                    'region_code',
                    'street_code',
                    'village_code',
                    'address_element_type',
                ],
                'required'
            ],
            [
                [
                    'code',
                    'short',
                    'area_code',
                    'city_code',
                    'region_code',
                    'street_code',
                    'village_code',
                    'address_element_type',
                ],
                'string',
                'max' => 100
            ],
            [
                [
                    'name',
                    'alt_name',
                ],
                'string',
                'max' => 1000
            ],
            [
                [
                    'fias_id',
                    'parent_fias_id',
                ],
                'string',
                'max' => 36
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'address_element_type' => 'Тип адресного элемента',
            'region_code' => 'Код региона в коде',
            'area_code' => 'Код района в коде',
            'city_code' => 'Код города в коде',
            'village_code' => 'Код населенного пункта в коде',
            'street_code' => 'Код улицы в коде',
            'code' => 'Код',
            'name' => 'Наименование',
            'short' => 'Сокращение',
            'zip_code' => 'Индекс',
            'alt_name' => 'Альтернативные названия',
            'created_at' => Yii::t('common', 'Создано'),
            'updated_at' => Yii::t('common', 'Последнее обновление'),
        ];
    }

    public function getFullname()
    {
        return $this->name . ' ' . $this->short;
    }

    public function getArea()
    {
        return Fias::findOne([
            'region_code' => $this->region_code,
            'area_code' => $this->area_code,
            'city_code' => '0',
            'village_code' => '0',
            'street_code' => '0',
        ]);
    }

    public function getAreaName()
    {
        return ArrayHelper::getValue($this, 'area.name', '');
    }

    public function getAreaShort()
    {
        return ArrayHelper::getValue($this, 'area.short', '');
    }
}
