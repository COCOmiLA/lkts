<?php

namespace common\models;

use yii\base\Model;

class RecaptchaForm extends Model
{

    public $site_key_v2;
    public $site_key_v3;
    public $server_key_v2;
    public $server_key_v3;

    public $status;

    public function rules()
    {
        return [
            [
                [
                    'site_key_v2',
                    'site_key_v3',
                    'server_key_v2',
                    'server_key_v3',
                ],
                'string',
                'max' => 200
            ],
        ];
    }


    public function attributeLabels()
    {
        return [
            'site_key_v2' => 'Ключ сайта v2',
            'site_key_v3' => 'Ключ сайта v3',
            'server_key_v2' => 'Ключ сервера v2',
            'server_key_v3' => 'Ключ сервера v3',
        ];
    }
}
