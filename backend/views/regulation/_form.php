<?php

use common\components\ini\iniGet;
use common\components\RegulationRelationManager;
use common\models\Regulation;
use kartik\widgets\DepDrop;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;





$appLanguage = Yii::$app->language;

?>

<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'before_link_text')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'related_entity')->dropDownList(RegulationRelationManager::GetRelatedList(), [
            'id' => 'related_entity_field',
            'prompt' => 'Выберите связанную сущность'
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'attachment_type')->widget(DepDrop::class, [
            'language' => $appLanguage,
            'class' => 'form-control',
            'options' => [
                'id' => 'attachment_type_field',
                'placeholder' => 'Нет типа прикрепляемого документа'
            ],
            'pluginOptions' => [
                'depends' => ['related_entity_field'],
                'placeholder' => 'Нет типа прикрепляемого документа',
                'url' => $model->isNewRecord ? Url::to(['get-types']) : Url::to(['get-types', 'id' => $model->id]),
                'loadingText' => 'Загрузка ...',
                'initialize' => $model->id !== null,
                'params' => ['related_entity_field']
            ],
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'content_type')->dropDownList(Regulation::getContentTypes(), [
            'id' => 'content_type_field',
            'prompt' => 'Выберите тип содержимого'
        ]) ?>
    </div>
</div>


<div id="content-link" class="hidden regulation-content">
    <div class="row">
        <div class="col-sm-12 form-group">
            <?= $form->field($model, 'content_link')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
</div>

<div id="content-html" class="hidden regulation-content">
    <div class="row">
        <div class="col-sm-12 form-group">
            <?= $form->field($model, 'content_html')->textarea([
                'class' => 'form-controll',
                'style' => 'min-height: 100px; min-width:100%;max-width:100%;'
            ]) ?>
        </div>
    </div>
</div>

<div id="content-file" class="hidden regulation-content ">
    <div class="row">
        <div class="col-sm-12 form-group">
            <?= $form->field($model, 'file')->widget(
                FileInput::class,
                [
                    'class' => 'regulationFileInput',
                    'language' => $appLanguage,
                    'options' => [
                        'multiple' => false,
                        'id' => "content_file_fields"
                    ],
                    'pluginOptions' => [
                        'theme' => 'fa4',
                        'showClose' => false,
                        'showRemove' => true,
                        'showUpload' => false,
                        'showCaption' => true,
                        'showPreview' => true,
                        'overwriteInitial' => true,
                        'dropZoneEnabled' => false,
                        'maxFileCount' => 1,
                        'hideThumbnailContent' => false,
                        'removeClass' => 'btn btn-danger',
                        'removeFromPreviewOnError' => true,
                        'initialPreviewAsData' => true,
                        'initialPreview' => (empty($model->id)) ? false : Url::to(['download-regulation-file', 'id' => $model->id]),
                        'initialCaption' => (empty($model->id)) ? false : $model->content_file,
                        'initialPreviewConfig' => [
                            [
                                'caption' => (empty($model->id)) ? false : $model->content_file,
                                'type' => $model->content_file_extension === 'pdf' ? 'pdf' : 'image',
                            ]
                        ],
                        'initialPreviewFileType' => $model->content_file_extension === 'pdf' ? 'pdf' : 'image',
                        'maxFileSize' => iniGet::getUploadMaxFilesize(),
                        'allowedFileExtensions' => \common\models\Attachment::getExtensionsList(),
                    ],
                ]
            ) ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 form-group">
        <?= $form->field($model, 'confirm_required')->checkbox() ?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>


<style>
    .hidden {
        display: none
    }

    .shown {
        display: block;
    }
</style>
<?php
$js = "const CONTENT_TYPE_HTML = " . Regulation::CONTENT_TYPE_HTML . "\n";
$js .= "const CONTENT_TYPE_FILE = " . Regulation::CONTENT_TYPE_FILE . "\n";
$js .= "const CONTENT_TYPE_LINK = " . Regulation::CONTENT_TYPE_LINK . "\n";
$js .= <<<JS
    $("#content_type_field").change(function () {
    var selected = this.options[this.selectedIndex].value;
    changeContentBlock(selected);
});
$(document).ready(function () {
    var select = $("#content_type_field")[0];
    var selected = select.options[select.selectedIndex].value;
    changeContentBlock(selected);
});

function hideAll() {
    $(".regulation-content").addClass("hidden");
}

function changeContentBlock(selected) {
    hideAll();

    switch (+selected) {
        case CONTENT_TYPE_HTML:
            $("#content-html").removeClass("hidden");
            break;

        case CONTENT_TYPE_FILE:
            $("#content-file").removeClass("hidden");
            break;

        case CONTENT_TYPE_LINK:
            $("#content-link").removeClass("hidden");
            break;

        default:
            break;
    }
}

JS;

$this->registerJs($js, View::POS_END);