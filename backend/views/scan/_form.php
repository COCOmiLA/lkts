<?php

use common\components\ini\iniGet;
use common\models\AttachmentType;
use common\models\AttachmentTypeTemplate;
use common\models\dictionary\DocumentType;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;







$appLanguage = Yii::$app->language;

$attachmentTypeTemplate = $model->getOrBuildAttachmentTypeTemplate();
$showPreview = $attachmentTypeTemplate->hasFile();
$fileUrl = $attachmentTypeTemplate->getDownloadUrl();
$deleteUrl = $attachmentTypeTemplate->getDeleteUrl();
$caption = $showPreview ? ArrayHelper::getValue($model, 'linkedFile.upload_name', '') : '';

$documentTypesOptions = [];

if ($model->document_type_guid && !isset($document_types[$model->document_type_guid])) {
    [
        'description' => $description,
        'documentTypesOptions' => $documentTypesOptions,
    ] = DocumentType::processArchiveDocForDropdown('ref_key', $model->document_type_guid);
    $document_types[$model->document_type_guid] = $description;

    if ($documentTypesOptions) {
        $model->addError(
            'document_type_guid',
            Yii::t(
                'backend',
                'Внимание! Выбранный элемент "{attribute}" находится в архиве.',
                ['attribute' => $model->getAttributeLabel('document_type_guid')]
            )
        );
    }
}

$form = ActiveForm::begin();

echo $form->field($model, 'name')
    ->textInput(['disabled' => $model->admissionCampaignRef != null]);

echo $form->field($model, 'related_entity')
    ->dropDownList($entities);

echo $form->field($model, 'document_type_guid')
    ->dropDownList($document_types, [
        'options' => $documentTypesOptions,
        'disabled' => $model->admissionCampaignRef != null,
        'prompt' => 'Выберите тип документа из Информационной системы вуза ...',
    ]);

echo $form->field($model, 'tooltip_description')
    ->textarea();

echo $form->field($model, 'required')
    ->checkbox(['label' => 'Обязательно для прикрепления', 'disabled' => $model->admissionCampaignRef != null]);

if ($model->from1c) {
    echo $form->field($model, 'hidden')
        ->checkbox(['label' => 'Скрыть']);
};

echo $form->field($model, 'allow_add_new_file_after_app_approve')
    ->checkbox();

echo $form->field($model, 'allow_delete_file_after_app_approve')
    ->checkbox();

echo $form->field($attachmentTypeTemplate, 'file')
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
