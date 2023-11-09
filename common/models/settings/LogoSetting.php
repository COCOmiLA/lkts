<?php

namespace common\models\settings;

use common\components\FilesWorker\FilesWorker;
use common\models\errors\RecordNotValid;
use Throwable;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;











class LogoSetting extends ActiveRecord
{
    
    public static $LOGO_FILE_URL = '/img';

    
    public $logoFile;

    


    public static function tableName()
    {
        return '{{%logo_setting}}';
    }

    


    public function rules()
    {
        return [
            [
                [
                    'width',
                    'height',
                ],
                'integer'
            ],
            [
                [
                    'name',
                    'description',
                ],
                'string',
                'max' => 100
            ],
            [
                'extension',
                'string',
                'max' => 5
            ],
            [
                'logoFile',
                'file',
                'skipOnEmpty' => true,
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'width' => Yii::t(
                'backend',
                'Ширина в пикселях для «{appearanceFileName}»',
                ['appearanceFileName' => Yii::t('backend', $this->description)]
            ),
            'height' => Yii::t(
                'backend',
                'Высота в пикселях для «{appearanceFileName}»',
                ['appearanceFileName' => Yii::t('backend', $this->description)]
            ),
            'description' => Yii::t('backend', 'Описание'),
            'logoFile' => Yii::t('backend', $this->description),
        ];
    }

    public function upload()
    {
        $separator = DIRECTORY_SEPARATOR;
        $frontendWeb = FileHelper::normalizePath(Yii::getAlias('@frontend/web'));

        if ($this->validate()) {
            $path = urlencode($this->logoFile->name);
            $tmpExtension = pathinfo($path);
            $this->extension = ArrayHelper::getValue($tmpExtension, 'extension');

            if (isset($this->logoFile)) {
                return $this->logoFile->saveAs("{$frontendWeb}{$separator}img{$separator}{$this->name}.{$this->extension}");
            }

            return true;
        } else {
            return false;
        }
    }

    


    public function getLogoFileUrl(): string
    {
        return LogoSetting::$LOGO_FILE_URL . "/{$this->name}.{$this->extension}";
    }

    


    public function hasAppearanceFile(): bool
    {
        $frontendWeb = Yii::getAlias("@frontend/web/img/{$this->name}.{$this->extension}");
        return FilesWorker::hasFile($frontendWeb);
    }

    


    public static function logoDirIsWritable(): bool
    {
        $path = FileHelper::normalizePath(Yii::getAlias('@frontend/web/img'));
        return is_writable($path);
    }

    




    public static function loadFromPost(array $postData): bool
    {
        $logoSettings = LogoSetting::find()->all();
        if (!$logoSettings) {
            return true;
        }

        foreach ($logoSettings as $logoSetting) {
            

            $data = ArrayHelper::getValue($postData, "{$logoSetting->formName()}.{$logoSetting->id}");
            if (!$logoSetting->load($data, '')) {
                Yii::$app->session->setFlash(
                    'alert',
                    [
                        'body' => Yii::t('backend', 'Ошибка загрузки настроек'),
                        'options' => ['class' => 'alert-error']
                    ]
                );

                return false;
            }

            $logoSetting->logoFile = UploadedFile::getInstanceByName("{$logoSetting->formName()}[{$logoSetting->id}][logoFile]");
            if (isset($logoSetting->logoFile)) {
                if (!$logoSetting->upload()) {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'body' => Yii::t('backend', 'Ошибка сохранения файла'),
                            'options' => ['class' => 'alert-error']
                        ]
                    );

                    return false;
                }
            }


            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$logoSetting->save()) {
                    throw new RecordNotValid($logoSetting);
                }

                $transaction->commit();
            } catch (Throwable $th) {
                $transaction->rollBack();

                Yii::error(
                    "Ошибка при сохранении логотипа `{$logoSetting->description}`: {$th->getMessage()}",
                    'LogoSetting.deleteFile'
                );

                Yii::$app->session->setFlash(
                    'alert',
                    [
                        'body' => Yii::t('backend', 'Ошибка сохранения настроек логотипа'),
                        'options' => ['class' => 'alert-error']
                    ]
                );

                return false;
            }
        }

        return true;
    }

    


    public function deleteFileUrl(): string
    {
        return Url::to(['delete-logo', 'id' => $this->id]);
    }

    


    public function deleteFile(): void
    {
        $path = FileHelper::normalizePath(Yii::getAlias("@frontend/web/img/{$this->name}.{$this->extension}"));

        if (is_link($path)) {
            return;
        }

        if (!unlink($path)) {
            return;
        }

        $this->width = 0;
        $this->height = 0;
        $this->extension = null;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save()) {
                throw new RecordNotValid($this);
            }

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error(
                "Ошибка при очистке данных для логотипа `{$this->description}`: {$th->getMessage()}",
                'LogoSetting.deleteFile'
            );

            $transaction->rollBack();

            Yii::$app->session->setFlash(
                'alert',
                [
                    'body' => Yii::t('backend', 'Ошибка очистки настроек логотипа'),
                    'options' => ['class' => 'alert-error']
                ]
            );
        }
    }
}
