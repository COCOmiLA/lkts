<?php

use backend\models\FaviconSettings;
use common\components\FilesWorker\FilesWorker;
use common\models\settings\LogoSetting;
use kartik\form\ActiveForm;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Html;
use yii\web\View;








$this->title = Yii::t('backend', 'Настройка оформления');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php if (!LogoSetting::logoDirIsWritable()) : ?>
    <?= Alert::widget([
        'options' => ['class' => 'alert-danger'],
        'body' => Yii::t('backend', 'Директория "frontend/web/img" не доступна для записи!!!'),
    ]) ?>
<?php endif; ?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $this->render('_file-input', [
    'form' => $form,
    'model' => $faviconModel,
    'fieldName' => 'faviconIcoFile',
    'allowedFileExtensions' => ['ico'],
    'fileUrl' => FaviconSettings::$FAVICON_ICO_URL,
    'deleteUrl' => $faviconModel->deleteFileUrl(),
]) ?>

<?php foreach ($logoModels as $logoModel) : ?>
    <hr>

    <?= $this->render('_file-input', [
        'form' => $form,
        'model' => $logoModel,
        'fieldName' => "[{$logoModel->id}]logoFile",
        'allowedFileExtensions' => FilesWorker::getAllowedImageExtensionsToUploadList(),
        'fileUrl' => $logoModel->getLogoFileUrl(),
        'deleteUrl' => $logoModel->deleteFileUrl(),
    ]) ?>

    <div class="row">
        <div class="col-12 col-sm-6">
            <?= $form->field($logoModel, "[{$logoModel->id}]width") ?>
        </div>

        <div class="col-12 col-sm-6">
            <?= $form->field($logoModel, "[{$logoModel->id}]height") ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="form-group">
    <?= Html::submitButton(
        Yii::t('backend', 'Сохранить'),
        ['class' => 'btn btn-success']
    ) ?>
</div>

<?php ActiveForm::end();
