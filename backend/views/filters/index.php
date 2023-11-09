<?php

use backend\models\FiltersSetting;
use kartik\form\ActiveForm;
use kartik\sortable\Sortable;
use yii\bootstrap4\Alert;
use yii\helpers\Html;

$this->registerJsFile(
    \common\helpers\Mix::mix('/js/sorted-page-element.js'),
    ['depends' => [
        '\common\assets\Flot',
        '\yii\web\JqueryAsset',
        '\yii\bootstrap4\BootstrapPluginAsset'
    ]]
);

$this->title = "Настроить отображения фильтров модератора";

?>

<?php if (empty($model->filters)) : ?>
    <?= Alert::widget([
        'options' => ['class' => 'alert-danger'],
        'body' => 'Список фильтров пустой',
    ]) ?>
<?php else : ?>
    <?php $form = ActiveForm::begin(['id' => 'sorted_element_form']) ?>

    <div class="row">
        <div class="col-8">
            <strong>Заголовок</strong>
        </div>

        <div class="col-2">
            <strong>Отображать колонку</strong>
        </div>

        <div class="col-2">
            <strong>Отображать фильтр</strong>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= Sortable::widget([
                'connected' => true,
                'items' => array_map(
                    function (FiltersSetting $filter) use ($form, $model) {
                        return $filter->buildField($form, $model);
                    },
                    $model->filters
                )
            ]); ?>
        </div>
    </div>

    <?= $form->field($model, 'sortablePageElements')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-12">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']) ?>
        </div>
    </div>

    <?php ActiveForm::end() ?>
<?php endif;
