<?php

namespace backend\models;

use common\components\FilesWorker\FilesWorker;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;






class FaviconSettings extends Model
{
    
    public static $FAVICON_ICO_URL = '/custom-favicon.ico';

    
    public $faviconIcoFile;

    


    public function rules()
    {
        return [[
            'faviconIcoFile',
            'file',
            'skipOnEmpty' => true,
        ]];
    }

    


    public function attributeLabels()
    {
        return  ['faviconIcoFile' => Yii::t('backend', 'Иконка вкладки в браузере')];
    }

    


    public function hasAppearanceFile(): bool
    {
        $frontendWeb = Yii::getAlias('@frontend/web/custom-favicon.ico');
        return FilesWorker::hasFile($frontendWeb);
    }

    public function upload()
    {
        $separator = DIRECTORY_SEPARATOR;
        $frontendWeb = FileHelper::normalizePath(Yii::getAlias('@frontend/web'));

        if ($this->validate()) {
            $this->faviconIcoFile->saveAs("{$frontendWeb}{$separator}custom-favicon.ico");

            return true;
        } else {
            return false;
        }
    }

    




    public function loadFromPost(array $postData): bool
    {
        $data = ArrayHelper::getValue($postData, $this->formName());
        if (!$this->load($data, '')) {
            Yii::$app->session->setFlash(
                'alert',
                [
                    'body' => Yii::t('backend', 'Ошибка загрузки иконки'),
                    'options' => ['class' => 'alert-error']
                ]
            );

            return false;
        }

        $this->faviconIcoFile = UploadedFile::getInstanceByName("{$this->formName()}[faviconIcoFile]");
        if (isset($this->faviconIcoFile)) {
            if (!$this->upload()) {
                Yii::$app->session->setFlash(
                    'alert',
                    [
                        'body' => Yii::t('backend', 'Ошибка сохранения файла иконки'),
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
        return Url::to('delete-icon');
    }

    


    public function deleteFile(): void
    {
        $separator = DIRECTORY_SEPARATOR;
        $path = FileHelper::normalizePath(Yii::getAlias('@frontend/web') . "{$separator}custom-favicon.ico");

        if (is_link($path)) {
            return;
        }

        unlink($path);
    }
}
