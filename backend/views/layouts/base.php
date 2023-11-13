<?php

use backend\assets\BackendAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;







BackendAsset::register($this);

$this->params['body-class'] = array_key_exists('body-class', $this->params) ?
    $this->params['body-class']
    : null;
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>

<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>

<?= Html::beginTag('body', [
    'class' => implode(' ', [
        ArrayHelper::getValue($this->params, 'body-class'),
        Yii::$app->keyStorage->get('backend.layout-fixed') ? 'layout-navbar-fixed' : null,
        Yii::$app->keyStorage->get('backend.dark-mode') ? 'dark-mode' : null,
        Yii::$app->keyStorage->get('backend.small-body-text') ? 'text-sm' : null,
        Yii::$app->keyStorage->get('backend.layout-collapsed-sidebar') ? 'sidebar-collapse' : null,
    ])
]) ?>

<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>

<?= Html::endTag('body') ?>

</html>

<?php $this->endPage();
