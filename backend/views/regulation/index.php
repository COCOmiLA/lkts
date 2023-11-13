<?php

use common\components\RegulationRelationManager;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;




$this->title = "Нормативные документы";
$this->params['breadcrumbs'][] = $this->title;
$confirmDelete = Yii::$app->session->getFlash('confirm_delete');

?>

<?php if (isset($confirmDelete)) : ?>
    <div class="alert alert-danger">
        <?php
        echo Html::beginForm(Url::toRoute(['delete', 'id' => $confirmDelete["id"]]))
        ?>
        <?php echo Html::hiddenInput('confirmed', 1); ?>
        <p>Вы уверены, что хотите удалить нормативный документ <strong>"<?= $confirmDelete['name'] ?>"</strong>?</p>
        <p>Все привязанные к этому типу нормативного документа файлы будут удалены или помечены на удаление.</p>
        <div class="confirm-actions">
            <input type="submit" class="btn btn-success" value="Удалить" />
            <button type="button" class="btn btn-primary" data-dismiss="alert" aria-label="Отмена">
                Отмена
            </button>
        </div>
        <?php echo Html::endForm(); ?>
    </div>
<?php endif; ?>

<p>
    <?= Html::a('Создать нормативный документ', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<?= GridView::widget([
    'hover' => true,
    'headerContainer' => ['class' => 'thead-light'],
    'tableOptions' => ['class' => 'table-sm'],
    'striped' => false,
    'summary' => false,
    'pager' => [
        'firstPageLabel' => '<<',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'lastPageLabel' => '>>',
    ],
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => \yii\grid\SerialColumn::class,
        ],

        'name',
        [
            'attribute' => 'confirm_required',
            'value' => function ($model) {
                return $model->getConfirmRequiredText();
            },
        ],
        [
            'attribute' => 'related_entity',
            'value' => function ($model) {
                return RegulationRelationManager::GetRelatedTitle($model->related_entity);
            },
        ],
        [
            'attribute' => 'content_type',
            'value' => function ($model) {
                return $model->getContentTypeText();
            },
        ],

        ['class' => \yii\grid\ActionColumn::class],
    ],
]);
