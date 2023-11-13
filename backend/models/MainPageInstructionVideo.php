<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;



















class MainPageInstructionVideo extends MainPageInstructionFile
{
    public const ACCEPT_FILE_EXTENSIONS = '3gp, avi, flv, gvi, m2v, m4v, mkv, mov, mp4, mpg, ogv, webm, wmv, mpeg';

    


    public static function tableName()
    {
        return '{{%main_page_instruction_video}}';
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
                    [
                        'autoplay',
                        'muted',
                        'loop',
                    ],
                    'default',
                    'value' => false
                ],
                [
                    [
                        'autoplay',
                        'muted',
                        'loop',
                    ],
                    'boolean'
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
            'file' => Yii::t('backend', 'Видео'),
            'loop' => Yii::t('backend', 'Цикличное воспроизведение'),
            'autoplay' => Yii::t('backend', 'Автоматически воспроизводить видео'),
            'muted' => Yii::t('backend', 'По умолчанию видео воспроизводится без звука'),
        ];
    }

    




    public function getMainPageSetting()
    {
        return $this->hasOne(MainPageSetting::class, ['id' => 'main_page_setting_id']);
    }

    


    public function getViewFileName(): string
    {
        return '_instruction_point_video';
    }

    


    public function buildAdditionalHtmlAttributes(): string
    {
        $buildAttributes = ['loop', 'muted', 'autoplay'];

        $additionalAttributes = '';
        foreach ($buildAttributes as $buildAttribute) {
            if ($this->{$buildAttribute}) {
                $additionalAttributes .= "{$buildAttribute} ";
            }
        }

        return $additionalAttributes;
    }
}
