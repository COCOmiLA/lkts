<?php

namespace common\models\settings;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;






class StudentSideLinks extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%student_side_links}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'url',
                    'description'
                ],
                'string',
                'max' => 1000
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'url' => 'Адрес',
            'number' => 'Номер',
            'description' => 'Описание'
        ];
    }

    public function isActive()
    {
        if (strlen((string)$this->url) > 0) {
            return true;
        }
        return false;
    }

    


    public static function getAllLinks()
    {
        $links = StudentSideLinks::find()->all();
        if (empty($links)) {
            return [];
        }
        $links = ArrayHelper::toArray($links, [
            'app\models\Post' => [
                'url',
                'number',
                'description'
            ],
        ]);

        $routes = [];
        foreach ($links as $link) {
            $url = '';
            if (!empty($link['number'])) {
                $url = "{$link['number']}: ";
            }
            $url .= $link['description'];
            $routes[$link['url']] = $url;
        }

        return $routes;
    }

    


    public function getComponentName(): ?string
    {
        return $this->description;
    }

    


    public function getBaseRoute(): ?string
    {
        return $this->url;
    }
}
