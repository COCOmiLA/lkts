<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;












class MainPageInstructionText extends MainPageInstructionTemplate
{
    


    public static function tableName()
    {
        return '{{%main_page_instruction_text}}';
    }

    


    public function rules()
    {
        return [
            [
                ['main_page_setting_id'],
                'required'
            ],
            [
                [
                    'main_page_setting_id',
                    'created_at',
                    'updated_at',
                    'paragraph',
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'main_page_setting_id',
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                ['paragraph'],
                'string',
            ],
            [
                ['main_page_setting_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => MainPageSetting::class,
                'targetAttribute' => ['main_page_setting_id' => 'id']
            ],
        ];
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return ['paragraph' => Yii::t('backend', 'Параграф')];
    }

    




    public function getMainPageSetting()
    {
        return $this->hasOne(MainPageSetting::class, ['id' => 'main_page_setting_id']);
    }

    


    public function getViewFileName(): string
    {
        return '_instruction_point_text';
    }
}
