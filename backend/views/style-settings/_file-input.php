<?php

use backend\models\FaviconSettings;
use common\components\ini\iniGet;
use common\models\settings\LogoSetting;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\web\View;












$appLanguage = Yii::$app->language;

$showPreview = $model->hasAppearanceFile();

?>

<div class="row">
    <div class="col-12">
        <?= $form->field($model, $fieldName)->widget(
            FileInput::class,
            [
                'language' => $appLanguage,
                'options' => ['multiple' => false],
                'pluginOptions' => [
                    'maxFileCount' => 1,
                    'showClose' => false,
                    'showRemove' => false,
                    'showUpload' => false,
                    'showCaption' => true,
                    'showPreview' => true,
                    'dropZoneEnabled' => false,
                    'overwriteInitial' => true,
                    'hideThumbnailContent' => false,
                    'removeFromPreviewOnError' => true,
                    'initialPreviewFileType' => 'image',
                    'initialPreviewAsData' => $showPreview,
                    'maxFileSize' => iniGet::getUploadMaxFilesize(),
                    'allowedFileExtensions' => $allowedFileExtensions,
                    'deleteUrl' => $showPreview ? $deleteUrl : $showPreview,
                    'initialCaption' => $showPreview ? $fileUrl : $showPreview,
                    'initialPreview' => $showPreview ? $fileUrl : $showPreview,
                    'initialPreviewConfig' => $showPreview ? [[
                        'type' => 'image',
                        'caption' => $fileUrl,
                    ]] : $showPreview,
                ],
            ]
        ) ?>
    </div>
</div>