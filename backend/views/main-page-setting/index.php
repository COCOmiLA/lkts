<?php

use backend\assets\MainPageSettingAsset;
use backend\models\MainPageInstructionHeader;
use backend\models\MainPageSetting;
use kartik\form\ActiveForm;
use kartik\sortable\Sortable;
use yii\web\View;








MainPageSettingAsset::register($this);

$this->title = Yii::t('backend', 'Настройка главной страницы поступающего');

$form = ActiveForm::begin(['id' => 'template_element_form']);

$instructionPointHeader = $this->render(
    '_instruction_point_header',
    [
        'form' => $form,
        'model' => $settingModel->getOrBuildHeader(),
    ]
);
$instructionPointText = $this->render(
    '_instruction_point_text',
    [
        'form' => $form,
        'model' => $settingModel->getOrBuildText(),
    ]
);
$instructionPointImage = $this->render(
    '_instruction_point_image',
    [
        'form' => $form,
        'model' => $settingModel->getOrBuildImage(),
    ]
);
$instructionPointVideo = $this->render(
    '_instruction_point_video',
    [
        'form' => $form,
        'model' => $settingModel->getOrBuildVideo(),
    ]
);

ActiveForm::end();

?>

<div class="col-12">
    <div class="row">
        <h4>
            <?= Yii::t('backend', 'Шаблоны') ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?= Sortable::widget([
            'items' => [
                ['content' => $instructionPointHeader],
                ['content' => $instructionPointImage],
                ['content' => $instructionPointVideo],
                ['content' => $instructionPointText],
            ],
            'options' => ['class' => 'instruction-step bg-info text-black'],
            'pluginOptions' => [
                'copy' => true,
                'acceptFrom' => false,
            ]
        ]); ?>
    </div>
</div>

<hr>

<div class="col-12">
    <div class="row">
        <h4>
            <?= Yii::t('backend', 'Макет') ?>
        </h4>
    </div>
</div>

<?= $this->render(
    '_instruction_layout_form',
    compact([
        'instructions',
        'settingModel',
    ])
); ?>

<hr>

<div class="col-12">
    <div class="row">
        <h4>
            <?= Yii::t('backend', 'Корзина') ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?= Sortable::widget([
            'items' => [],
            'options' => ['class' => 'alert alert-danger'],
            'pluginOptions' => ['acceptFrom' => implode(',', [
                '.instructions',
                '.instruction-step',
            ])]
        ]); ?>
    </div>
</div>