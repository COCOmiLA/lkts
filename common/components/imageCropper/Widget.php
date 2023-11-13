<?php

namespace common\components\imageCropper;

use common\components\imageCropper\assets\CropperAsset;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use Yii;
use yii\helpers\ArrayHelper;

class Widget extends InputWidget
{
    public $uploadParameter = 'file';
    public $width = 200;
    public $height = 200;
    public $label = '';
    public $uploadUrl;
    public $deleteUrl = false;
    public $isReadonly = false;
    public $noPhotoImage = '';
    public $maxSize = 2097152;
    public $thumbnailWidth = 200;
    public $thumbnailHeight = 200;
    public $cropAreaWidth = 200;
    public $cropAreaHeight = 200;
    public $extensions = 'jpeg, jpg, png, gif';
    public $onCompleteJcrop;
    public $pluginOptions = [];
    public $aspectRatio = null;
    public $isPdfFile = false;

    


    public function init()
    {
        parent::init();

        if ($this->uploadUrl === null) {
            throw new InvalidConfigException(Yii::t('cropper', 'MISSING_ATTRIBUTE: `Атрибут "{attribute}" должен быть указан`', ['attribute' => 'uploadUrl']));
        } else {
            $this->uploadUrl = rtrim(Yii::getAlias($this->uploadUrl), '/') . '/';
        }

        if ($this->label == '') {
            $this->label = Yii::t('cropper', 'DEFAULT_LABEL: `Для загрузки новой фотографии кликните здесь или перетащите файл сюда`');
        }
    }

    


    public function run()
    {
        $this->registerClientAssets();

        return $this->render('widget', [
            'model' => $this->model,
            'widget' => $this
        ]);
    }

    


    public function registerClientAssets()
    {
        $view = $this->getView();
        $assets = CropperAsset::register($view);

        $settings = array_merge([
            'isReadonly' => $this->isReadonly,
            'url' => $this->uploadUrl,
            'deleteUrl' => $this->deleteUrl,
            'name' => $this->uploadParameter,
            'maxSize' => $this->maxSize / 1024,
            'allowedExtensions' => explode(', ', $this->extensions),
            'size_error_text' => Yii::t('cropper', 'TOO_BIG_ERROR: `Превышен допустимый размер загружаемого файла ({size} Мб)`', ['size' => $this->maxSize / (1024 * 1024)]),
            'ext_error_text' => Yii::t('cropper', 'EXTENSION_ERROR: `Разрешены только следующие форматы файлов: {formats}`', ['formats' => $this->extensions]),
            'accept' => 'image/*',
        ], $this->pluginOptions);

        $attachmentTypeTemplate = ArrayHelper::getValue($this->model, 'attachmentType.attachmentTypeTemplate');
        if ($attachmentTypeTemplate) {
            $this->noPhotoImage = $attachmentTypeTemplate->getDownloadUrl();
            $linkedFileUploadName = ArrayHelper::getValue($attachmentTypeTemplate, 'linkedFile.upload_name', '');
            if (strpos($linkedFileUploadName, '.pdf') !== false) {
                $this->isPdfFile = true;
                $settings['accept'] = 'pdf';
            } else {
                $settings['accept'] = 'image/*';
            }
        }
        if ($this->noPhotoImage == '') {
            $this->noPhotoImage = $assets->baseUrl . '/img/nophoto.png';
            $settings['accept'] = 'image/*';
        }

        if (is_numeric($this->aspectRatio)) {
            $settings['aspectRatio'] = $this->aspectRatio;
        }

        if ($this->onCompleteJcrop)
            $settings['onCompleteJcrop'] = $this->onCompleteJcrop;

        $view->registerJs(
            'jQuery("#' . $this->options['id'] . '").parent().find(".new-photo-area").cropper(' . Json::encode($settings) . ', ' . $this->width . ', ' . $this->height . ');',
            $view::POS_READY
        );
    }
}
