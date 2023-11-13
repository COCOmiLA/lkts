<?php

use common\components\RegulationRelationManager;
use common\models\Regulation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;







YiiAsset::register($this);

$this->title = 'Просмотр нормативного документа';
$this->params['breadcrumbs'][] = ['label' => 'Нормативные документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
    <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

    <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Вы уверены что хотите удалить этот элемент?',
            'method' => 'post',
        ],
    ]) ?>

    <?= Html::a('К списку документов', ['/regulation'], ['class' => 'btn btn-success float-right']) ?>
</p>

<?= DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'name',
        [
            'label' => $model->getAttributeLabel('content_type'),
            'value' => $model->getContentTypeText(),
        ],
        [
            'label' => $model->getAttributeLabel('related_entity'),
            'value' => RegulationRelationManager::GetRelatedTitle($model->related_entity),
        ],
        [
            'label' => $model->getAttributeLabel('confirm_required'),
            'value' => $model->getConfirmRequiredText(),
        ],
        [
            'attribute' => 'before_link_text',
            'format' => 'raw',
            'value' => !empty($model->before_link_text) ? $model->before_link_text : '(Не задано)',
        ],
        [
            'attribute' => 'content_link',
            'format' => 'raw',
            'value' => !empty($model->content_link) ? $model->content_link : '(Не задано)',
        ],
        [
            'attribute' => 'content_html',
            'format' => 'raw',
            'value' => !empty($model->content_html) ? $model->content_html : '(Не задано)',
        ],
        [
            'format' => 'raw',
            'label' => $model->getAttributeLabel('content_file'),
            'value' =>  !empty($model->content_file) ? Html::a($model->content_file, Url::to(['download-regulation-file', 'id' => $model->id]), [
                'download' => true,
            ]) :  '(Не задано)',
        ],
        [
            'format' => 'raw',
            'label' => $model->getAttributeLabel('attachment_type'),
            'value' => $model->attachmentType === null ? '(Не задано)' : $model->attachmentType->name,
        ],
    ],
]);