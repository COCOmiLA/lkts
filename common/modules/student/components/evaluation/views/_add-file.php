<?php

use common\components\ini\iniGet;
use common\modules\student\components\evaluation\models\UploadForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use kartik\form\ActiveForm;

$form = ActiveForm::begin([
    'action' => '/student/evaluation/upload',
    'options' => ['enctype' => 'multipart/form-data', 'id' => 'add_file_id']
]);

$upForm = new UploadForm();

echo Html::beginTag('div', ['class' => 'col-sm-12']);
echo $form->field($model, 'file')->widget(
    FileInput::class,
    [
        'language' => 'ru',
        'options' => [
            'multiple' => false,
        ],
        'pluginOptions' => [
            'theme' => 'fa4',
            'showClose' => false,
            'showRemove' => true,
            'showUpload' => false,
            'showPreview' => true,
            'showCaption' => true,
            'dropZoneEnabled' => false,
            'overwriteInitial' => false,
            'initialPreviewAsData' => true,
            'hideThumbnailContent' => true,
            'removeClass' => 'btn btn-danger',
            'removeFromPreviewOnError' => true,
            'maxFileSize' => iniGet::getUploadMaxFilesize(),
            'allowedFileExtensions' => UploadForm::getExtensionsList(),
        ]
    ]
);
echo $form->field($model, 'description');

echo Html::activeHiddenInput($model, 'uid');
echo Html::activeHiddenInput($model, 'luid');
echo Html::activeHiddenInput($model, 'puid');
echo Html::activeHiddenInput($model, 'studentId');
echo Html::activeHiddenInput($model, 'recordbook_id');
echo Html::endTag('div'); ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary float-right']) ?>
        </div>
    </div>

<?php ActiveForm::end();