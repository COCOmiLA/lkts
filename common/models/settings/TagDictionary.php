<?php

namespace common\models\settings;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;














class TagDictionary extends ActiveRecord
{
    const ABITURIENT_ROLE = 'abiturient';
    const MODERATOR_ROLE = 'moderator';

    


    public static function tableName()
    {
        return '{{%tag_dictionary}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'created_at',
                    'updated_at',
                ],
                'required'
            ],
            [
                [
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                ['url_template'],
                'string',
                'max' => 255
            ],
            [
                ['description'],
                'string',
                'max' => 500
            ],
            [
                [
                    'tag',
                    'role',
                    'default_alias',
                ],
                'string',
                'max' => 50
            ],
            [
                ['icon'],
                'string',
                'max' => 100
            ],
            [
                'role',
                'default',
                'value' => TagDictionary::ABITURIENT_ROLE
            ],
            [
                ['role'],
                'in',
                'range' => TagDictionary::getRoleRange()
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getUrl(int $id): string
    {
        if (empty($this->url_template)) {
            return '';
        }

        $urlParams = json_decode(
            strtr(
                $this->url_template,
                ['{{ID}}' => $id],
            ),
            true
        );
        return Url::to($urlParams);
    }

    


    public static function getRoleRange(): array
    {
        return [
            'Поступающий' => TagDictionary::ABITURIENT_ROLE,
            'Модератор' => TagDictionary::MODERATOR_ROLE,
        ];
    }
}
