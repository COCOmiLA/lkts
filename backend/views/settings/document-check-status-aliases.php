<?php

use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Настройки псевдонимов статусов проверки документа');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $form = ActiveForm::begin(); ?>

<?php foreach ($settings as $setting) : ?>
    <?php  ?>

    <div class="d-flex flex-row justify-content-between">
        <div class="flex-fill p-2">
            <?= $form->field($setting, "[{$setting->id}]humanReadableName"); ?>
        </div>

        <div class="flex-fill p-2">
            <?= $form->field($setting, "[{$setting->id}]icon_class"); ?>
        </div>

        <div class="flex-fill p-2">
            <?= $form->field($setting, "[{$setting->id}]icon_color")
                ->dropDownList(
                    StoredDocumentCheckStatusReferenceType::getIconColorList(),
                    [
                        'prompt' => Yii::t('backend', 'Выберите ...'),
                        'options' => StoredDocumentCheckStatusReferenceType::getIconColorListOptions(),
                    ]
                ); ?>
        </div>

        <div class="flex-shrink-1 p-2 d-flex justify-content-center align-items-center">
            <?= $setting->getIcon(); ?>
        </div>
    </div>
<?php endforeach; ?>

<?= Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']); ?>

<?php ActiveForm::end();
