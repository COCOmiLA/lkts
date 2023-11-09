<?php

use common\components\ini\iniGet;
use common\models\AttachmentTypeTemplate;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;







$appLanguage = Yii::$app->language;

$showPreview = $model->hasFile();
$fileUrl = $model->getDownloadUrl();
$deleteUrl = $model->getDeleteUrl();
$caption = $showPreview ? ArrayHelper::getValue($model, 'linkedFile.upload_name', '') : '';

$form = ActiveForm::begin();

echo $form->field($model, 'file')
    ->widget(
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
                'initialPreviewAsData' => $showPreview,
                'maxFileSize' => iniGet::getUploadMaxFilesize(),
                'deleteUrl' => $showPreview ? $deleteUrl : $showPreview,
                'initialCaption' => $showPreview ? $caption : $showPreview,
                'initialPreview' => $showPreview ? $fileUrl : $showPreview,
                'allowedFileExtensions' => AttachmentTypeTemplate::ACCEPT_FILE_EXTENSIONS,
                'initialPreviewConfig' => $showPreview ? [['caption' => $caption]] : $showPreview,
            ],
        ]
    );

?>

<div class="form-group">
    <?php echo Html::submitButton(
        Yii::t('backend', 'Сохранить'),
        ['class' => 'btn btn-primary', 'name' => 'addscan-button']
    ) ?>
</div>

<?php ActiveForm::end();
