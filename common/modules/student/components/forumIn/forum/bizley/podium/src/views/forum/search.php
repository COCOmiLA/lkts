<?php








use yii\helpers\Html;

if (!isset($author)) {
    $author = '';
}
if (!isset($model)) {
    $type    = 'posts';
    $display = 'topics';
}
else {
    $type    = $model->type;
    $display = $model->display;
}
?>
<?php if (!empty($dataProvider)): ?>
<?php
$typeName = $type == 'topics' ? Yii::t('podium/view', 'threads') : Yii::t('podium/view', 'posts');
if (!empty($query) && !empty($author)) {
    $this->title = Yii::t('podium/view', 'Search for {type} with "{query}" by "{author}"', ['query' => Html::encode($query), 'author' => Html::encode($author), 'type' => $typeName]);
}
elseif (!empty($query) && empty($author)) {
    $this->title = Yii::t('podium/view', 'Search for {type} with "{query}"', ['query' => Html::encode($query), 'type' => $typeName]);
}
elseif (empty($query) && !empty($author)) {
    $this->title = Yii::t('podium/view', 'Search for {type} by "{author}"', ['author' => Html::encode($author), 'type' => $typeName]);
}
else {
    $this->title = Yii::t('podium/view', 'Search for {type}', ['type' => $typeName]);
}
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Search Forum'), 'url' => ['forum/search']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-xs-12">
<?php switch ($display): ?>
<?php case 'posts': ?>
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search_posts', ['dataProvider' => $dataProvider, 'query' => $query, 'author' => $author, 'type' => $type]) ?>
        </div>
<?php break; default: ?>
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search_topics', ['dataProvider' => $dataProvider, 'query' => $query, 'author' => $author, 'type' => $type]) ?>
        </div>
<?php endswitch; ?>
    </div>
</div>
<?php else: ?>
<?php
$this->title = Yii::t('podium/view', 'Search Forum');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
<div class="col-12">
<?= $this->render('/elements/search/_search', ['model' => $model, 'list' => $list]) ?>
</div>
</div>
<?php endif;
