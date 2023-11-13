<?php

use common\components\ini\iniGet;
use common\modules\student\components\portfolio\models\UploadForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="row">
    <div class="col-12">
        <?php $form = ActiveForm::begin([
            'action' => '/student/portfolio/upload',
            'options' => ['enctype' => 'multipart/form-data', 'id' => 'add_file_id']
        ]);

        $upForm = new UploadForm();

        echo Html::beginTag('div', ['class' => 'row']);
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
                    'allowedFileExtensions' => UploadForm::getExtensionsList(),
                    'removeClass' => 'btn btn-danger',
                    'overwriteInitial' => false,
                    'initialPreviewAsData' => true,
                    'removeFromPreviewOnError' => true,
                    'hideThumbnailContent' => true,
                    'showPreview' => true,
                    'showCaption' => true,
                    'showUpload' => false,
                    'showRemove' => true,
                    'showClose' => false,
                    'maxFileSize' => iniGet::getUploadMaxFilesize(),
                    'dropZoneEnabled' => false,
                ]
            ]
        );
        echo Html::endTag('div');
        echo Html::beginTag('div', ['class' => 'col-sm-12']);
        echo $form->field($model, 'description');
        echo Html::endTag('div');
        echo Html::endTag('div');

        echo Html::activeHiddenInput($model, 'uid');
        echo Html::activeHiddenInput($model, 'luid');
        echo Html::activeHiddenInput($model, 'puid');
        echo Html::activeHiddenInput($model, 'studentId');
        echo Html::activeHiddenInput($model, 'recordbook_id');
        ?>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary float-right']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>
