<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;
















class MainPageInstructionImage extends MainPageInstructionFile
{
    public const ACCEPT_FILE_EXTENSIONS = 'gif, jpg, pjp, png, svg, apng, avif, jfif, jpeg, webp, pjpeg';

    


    public static function tableName()
    {
        return '{{%main_page_instruction_image}}';
    }

    


    public function rules()
    {
        return array_merge(
            [
                [
                    [
                        'width',
                        'height',
                        'created_at',
                        'updated_at',
                        'main_page_setting_id',
                    ],
                    'default',
                    'value' => null
                ],
                [
                    [
                        'width',
                        'height',
                        'created_at',
                        'updated_at',
                        'main_page_setting_id',
                    ],
                    'integer'
                ],
                [
                    ['main_page_setting_id'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => MainPageSetting::class,
                    'targetAttribute' => ['main_page_setting_id' => 'id']
                ],
            ],
            parent::rules()
        );
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return [
            'width' => Yii::t('backend', 'Ширина'),
            'height' => Yii::t('backend', 'Высота'),
            'file' => Yii::t('backend', 'Изображение'),
        ];
    }

    




    public function getMainPageSetting()
    {
        return $this->hasOne(MainPageSetting::class, ['id' => 'main_page_setting_id']);
    }

    


    public function getViewFileName(): string
    {
        return '_instruction_point_image';
    }
}
