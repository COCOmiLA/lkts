<?php

use yii\bootstrap4\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Обновление';
$this->params['breadcrumbs'][] = $this->title;

?>

<?= Html::tag('p', 'Версия портала: ' . $versionPortal); ?>

<?= Html::tag('p', 'Версия веб-сервисов 1С: ' . $version1C); ?>

<?= Html::tag('p', 'Версия PHP: ' . $versionPHP); ?>

<?= Html::tag('p', 'Операционная система: ' . php_uname('s') . ' ' . php_uname('v') . ' ' . php_uname('m')); ?>

<?php
$timeZoneLocal = date_default_timezone_get();
$timeZoneGlobal = ini_get('date.timezone');
if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) {
    echo Html::tag('div', '<strong>Внимание!</strong> Часовой пояс не установлен. Произведите настройку "date.timezone" в "php.ini"', ['class' => 'alert alert-danger']);
} else {
    echo Html::tag('p', "Установленный часовой пояс: {$timeZoneGlobal}");
}
?>

<?php if (Yii::$app->session->hasFlash('text-settings-changed')) {
    echo Alert::widget([
        'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('text-settings-changed'), 'body'),
        'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('text-settings-changed'), 'options'),
    ]);
} ?>

<?php
if (!empty($message)) {
    if ($result) {
        echo Html::tag(
            'div',
            $message,
            ['class' => 'alert alert-danger']
        );
    } else {
        echo Html::tag(
            'div',
            $message,
            ['class' => 'alert alert-success']
        );
    }
}

if ($result) {
    echo Html::a(
        'Применить изменения в БД',
        Url::toRoute(['update']),
        ['class' => "btn btn-primary"]
    );
}
?>
