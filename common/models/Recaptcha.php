<?php

namespace common\models;

use himiklab\yii2\recaptcha\ReCaptcha2;
use himiklab\yii2\recaptcha\ReCaptcha3;
use himiklab\yii2\recaptcha\ReCaptchaValidator2;
use himiklab\yii2\recaptcha\ReCaptchaValidator3;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;









class Recaptcha extends ActiveRecord
{
    public const RECAPTCHA_DISABLED = 1;
    public const RECAPTCHA_V2 = 2;
    public const RECAPTCHA_V3 = 3;

    


    public static function tableName()
    {
        return '{{%recaptcha}}';
    }

    


    public function rules()
    {
        return [
            [
                ['version'],
                'integer'
            ],
            [
                ['version'],
                'default',
                'value' => Recaptcha::RECAPTCHA_DISABLED
            ],
            [
                ['version'],
                'in',
                'range' => [
                    Recaptcha::RECAPTCHA_V2,
                    Recaptcha::RECAPTCHA_V3,
                    Recaptcha::RECAPTCHA_DISABLED,
                ]
            ],
            [
                ['name'],
                'string',
                'max' => 255
            ],
            [
                ['description'],
                'string',
                'max' => 50
            ],
            [
                [
                    'name',
                    'description',
                ],
                'trim',
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return ['version' => $this->description];
    }

    


    public static function radioItems(): array
    {
        return [
            ['label' => 'Откл.', 'value' => Recaptcha::RECAPTCHA_DISABLED],
            ['label' => 'V2', 'value' => Recaptcha::RECAPTCHA_V2],
            ['label' => 'V3', 'value' => Recaptcha::RECAPTCHA_V3],
        ];
    }

    




    public static function loadFromPost(array $postData = []): bool
    {
        if (empty(Yii::$app->db->getTableSchema('recaptcha'))) {
            return false;
        }

        $success = true;
        $recaptchas = Recaptcha::find()->all();
        foreach ($recaptchas as $recaptcha) {
            

            $I = $recaptcha->id;
            $formName = $recaptcha->formName();
            $data = ArrayHelper::getValue($postData, "{$formName}.{$I}");
            if ($recaptcha->load($data, '')) {
                if (!$recaptcha->save()) {
                    $success = false;

                    break;
                }
            } else {
                $success = false;

                break;
            }
        }

        return $success;
    }

    




    protected static function findVersionByName(string $name = ''): int
    {
        if (empty($name)) {
            return Recaptcha::RECAPTCHA_DISABLED;
        }
        $schema = Yii::$app->db->getTableSchema('recaptcha');
        if (!$schema || !isset($schema->columns['name'])) {
            return Recaptcha::RECAPTCHA_DISABLED;
        }
        return ArrayHelper::getValue(
            Recaptcha::findOne(['name' => $name]),
            'version',
            Recaptcha::RECAPTCHA_DISABLED
        );
    }

    




    public static function getValidationArrayByName(string $name = ''): array
    {
        $version = Recaptcha::findVersionByName($name);
        if ($version == Recaptcha::RECAPTCHA_DISABLED) {
            return [];
        }

        switch ($version) {
            case Recaptcha::RECAPTCHA_DISABLED:
                return [];

            case Recaptcha::RECAPTCHA_V2:
                return [
                    ['reCaptcha'],
                    ReCaptchaValidator2::class,
                    'secret' => (getenv('SERVER_KEY_V2') == false) ? '42' : getenv('SERVER_KEY_V2'),
                    'uncheckedMessage' => 'Пожалуйста, подтвердите что вы не робот.'
                ];

            case Recaptcha::RECAPTCHA_V3:
                return [
                    ['reCaptcha'],
                    ReCaptchaValidator3::class,
                    'secret' => (getenv('SERVER_KEY_V3') == false) ? '42' : getenv('SERVER_KEY_V3'),
                    'threshold' => 0.5,
                    'action' => $name,
                ];

            default:
                return [];
        }
    }

    




    public static function getWidgetParamsByName(string $name = ''): array
    {
        $version = Recaptcha::findVersionByName($name);
        if ($version == Recaptcha::RECAPTCHA_DISABLED) {
            return [];
        }

        switch ($version) {
            case Recaptcha::RECAPTCHA_DISABLED:
                return [];

            case Recaptcha::RECAPTCHA_V2:
                return [
                    'class' => ReCaptcha2::class,
                    'settings' => ['siteKey' => (getenv('SITE_KEY_V2') == false) ? '73' : getenv('SITE_KEY_V2')],
                ];

            case Recaptcha::RECAPTCHA_V3:
                return [
                    'class' => ReCaptcha3::class,
                    'settings' => [
                        'action' => $name,
                        'siteKey' => (getenv('SITE_KEY_V3') == false) ? '73' : getenv('SITE_KEY_V3'),
                    ],
                ];

            default:
                return [];
        }
    }

    




    public static function getInstance()
    {
        if (empty(\Yii::$app->db->getTableSchema('recaptcha'))) {
            return false;
        }

        $recapthca = Recaptcha::find()->limit(1)->one();

        if ($recapthca === null) {
            $recapthca = new Recaptcha();
            $recapthca->loadDefaultValues();
        }

        return $recapthca;
    }
}
