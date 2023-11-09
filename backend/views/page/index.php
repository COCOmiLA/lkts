<?php

use backend\models\SortedElementPage;
use kartik\sortable\Sortable;
use kartik\form\ActiveForm;
use yii\bootstrap4\Alert;
use yii\helpers\Html;
use yii\web\View;









$this->registerJsFile(
    \common\helpers\Mix::mix('/js/sorted-page-element.js'),
    ['depends' => [
        '\common\assets\Flot',
        '\yii\web\JqueryAsset',
        '\yii\bootstrap4\BootstrapPluginAsset'
    ]]
);

$this->title = "Настроить отображение элементов на главной странице у \"{$roleName}\"";

?>

<?php if (empty($role)) : ?>
    <?= Alert::widget([
        'options' => ['class' => 'alert-danger'],
        'body' => "Отсутствуют элементы для главной страницы \"{$roleName}\"",
    ]) ?>
<?php else : ?>
    <?php $newItems = $model->buildItemsArray($role, SortedElementPage::TYPE_NEW); ?>
    <?php if (!empty($newItems)) : ?>
        <div class="row">
            <div class="col-12">
                <p data-toggle="tooltip" data-placement="top" title="Новые элементы страницы '<?= $roleName ?>'">
                    Новые элементы страницы '<?= $roleName ?>'
                </p>
            </div>

            <div class="col-12">
                <?= Sortable::widget([
                    'connected' => true,
                    'options' => [
                        'id' => SortedElementPage::TYPE_NEW,
                        'class' => 'alert alert-info'
                    ],
                    'items' => $newItems
                ]); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-6">
            <p data-toggle="tooltip" data-placement="top" title="Элементы страницы '<?= $roleName ?>', которые будут расположены в левой колонке">
                Левая колонка
            </p>
        </div>

        <div class="col-sm-6">
            <p data-toggle="tooltip" data-placement="top" title="Элементы страницы '<?= $roleName ?>', которые будут расположены в правой колонке">
                Правая колонка
            </p>
        </div>

        <div class="col-sm-6">
            <?= Sortable::widget([
                'connected' => true,
                'options' => ['id' => SortedElementPage::TYPE_LEFT],
                'items' => $model->buildItemsArray($role, SortedElementPage::TYPE_LEFT)
            ]); ?>
        </div>

        <div class="col-sm-6">
            <?= Sortable::widget([
                'connected' => true,
                'options' => ['id' => SortedElementPage::TYPE_RIGHT],
                'items' => $model->buildItemsArray($role, SortedElementPage::TYPE_RIGHT)
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <p data-toggle="tooltip" data-placement="top" title="Элементы страницы '<?= $roleName ?>', которые будут скрыты для пользователя">
                Скрытые элементы
            </p>
        </div>

        <div class="col-md-12">
            <?= Sortable::widget([
                'connected' => true,
                'options' => [
                    'id' => SortedElementPage::TYPE_REMOVED,
                    'class' => 'alert alert-danger'
                ],
                'items' => $model->buildItemsArray($role, SortedElementPage::TYPE_REMOVED)
            ]); ?>
        </div>
    </div>

    <?php $form = ActiveForm::begin(['id' => 'sorted_element_form']) ?>

    <?= $form->field($model, 'sortablePageElements')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-12">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']) ?>
        </div>
    </div>

    <?php ActiveForm::end() ?>
<?php endif;
