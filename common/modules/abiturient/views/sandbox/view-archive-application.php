<?php

use common\modules\abiturient\assets\moderateAsset\ViewArchiveApplicationAsset;
use sguinfocom\widget\TreeView;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;








ViewArchiveApplicationAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'sandbox/view-archive-application/all',
    'Заголовок страницы просмотра архивов заявлений поступающего: `Просмотр архивов заявлений`'
);

$appLanguage = Yii::$app->language;

?>

<div class="row">
    <div class="col-12">
        <?php $url = Url::to(['/sandbox/moderate', 'id' => $id]);
        if ($currentUser->isViewer()) {
            $url = Url::toRoute(['viewer/view', 'id' => $id]);
        } ?>
        <?= Html::a(
            Yii::t(
                'sandbox/view-archive-application/all',
                'Текст подписи ссылки возврата на страницу проверки заявления; на стр. проверки анкеты поступающего: `Назад`'
            ),
            $url,
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php $template = '
            <div class="tree-view-wrapper">
                <div class="row">
                    <div class="col-12">{tree}</div>
                </div>
            </div>'; ?>
        <?php echo TreeView::widget([
            'data' => $applicationNodes,
            'header' => false,
            'id' => 'view_archive',
            'template' => $template,
            'size' => TreeView::SIZE_SMALL,
            'clientOptions' => [
                'expandIcon' => 'fa fa-caret-right',
                'collapseIcon' => 'fa fa-caret-down',
                'onRendered' => new JsExpression('
                    function() {
                        window.widthSetter();
                    }'),
                'onNodeExpanded' => new JsExpression('
                    function() {
                        window.widthSetter();
                    }'),
                'borderColor' => 'var(--white)',
                'enableLinks' => true,
                'levels' => 15,
            ],
        ]) ?>
    </div>
</div>