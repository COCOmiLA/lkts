<?php

use common\components\EnvironmentManager\exceptions\EnvironmentException;
use yii\helpers\Html;
use yii\web\View;














$timeZone = '';
$timeZoneLocal = date_default_timezone_get();
$timeZoneGlobal = ini_get('date.timezone');
if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) {
    $timeZone = Yii::t(
        'server/errors',
        'Текст сообщения ошибки сервера, при отсутствии часового пояса: `<strong>Внимание!</strong> Часовой пояс не установлен. Произведите настройку "date.timezone" в "php.ini"`'
    );
} else {
    $timeZone = Yii::t(
        'server/errors',
        'Текст сообщения ошибки сервера, с указанием часового пояса: `Установленный часовой пояс: {timeZoneGlobal}`',
        ['timeZoneGlobal' => $timeZoneGlobal]
    );
}

$this->title = $name;
$trace = nl2br($exception->getTraceAsString());

$textForVersionPortal = Yii::t(
    'server/errors',
    'Подпись для поля версии портала: `Версия портала`'
);
$textForVersion1C = Yii::t(
    'server/errors',
    'Подпись для поля версии ЛК: `Версия веб-сервисов 1С`'
);
$textForVersionPhp = Yii::t(
    'server/errors',
    'Подпись для поля версии PHP: `Версия PHP`'
);
$textForOsInfo = Yii::t(
    'server/errors',
    'Подпись для поля сведений об ОС: `Операционная система`'
);
$textForTimeZoneLocal = Yii::t(
    'server/errors',
    'Подпись для поля часового пояса: `Установленный часовой пояс`'
);
$showDeveloperInfo = \Yii::$app->supportInfo->showDeveloperInfo();

$support_info = \Yii::$app->supportInfo->render();

$developerInfo = <<<INFO
<div class="developer-info" id="developer-info">
   <p>{$support_info}</p>
   <p><strong>Error: {$exception->getMessage()}</strong></p>
    File: {$exception->getFile()}<br>
    Line: {$exception->getLine()}<br>
    <hr>
    Trace:<br> {$trace}
    <hr>
    <div class="alert alert-warning">
        <p>{$textForVersionPortal}: {$versionPortal}</p>
        <p>{$textForVersion1C}: {$version1C}</p>
        <p>{$textForVersionPhp}: {$versionPHP}</p>
        <p>{$textForOsInfo}: {$os_info}</p>
        <p>{$textForTimeZoneLocal}: {$timeZoneLocal}</p>
    </div>
</div>
INFO;

?>

<?php if ($exception instanceof EnvironmentException) : ?>
    <div class="error">
        <div class="row">
            <div class="col-12">
                <div class="error-content text-center d-flex">
                    <div class="alert alert-danger" style=" margin: auto">
                        <?= $exception->getMessage() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="site-error">
        <?php if (!$isTechnicalWorks) : ?>
            <h1>
                <?= Html::encode($this->title); ?>
            </h1>

            <?php if ($showDeveloperInfo): ?>
                <button class="btn btn-outline-secondary" id="btn-developer-info">
                    <?= Yii::t(
                        'server/errors',
                        'Подпись кнопки с деталями об ошибке для технической поддержки: `Информация для технической поддержки`'
                    ) ?>
                </button>

                <br>

                <?= $developerInfo; ?>
            <?php endif; ?>

            <div class="alert alert-danger">
                <?= nl2br(Html::encode($message)); ?>
            </div>
        <?php else : ?>
            <h2>
                <?= $this->title; ?>
            </h2>
            <?php if ($showDeveloperInfo): ?>
                <button class="btn btn-outline-secondary" id="btn-developer-info">
                    <?= Yii::t(
                        'server/errors',
                        'Подпись кнопки с деталями об ошибке для разработчика: `Информация для разработчика`'
                    ) ?>
                </button>

                <br>

                <?= $developerInfo; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php

    $js = <<<JS
    $(function(){
        var developerInfo = $('#developer-info');
        
        $('#btn-developer-info').click(function(e) {
            developerInfo.toggleClass('active');
        });
    });
JS;

    $this->registerJs($js, View::POS_END);

    $css = <<<CSS
    .developer-info{
        display: none;
        border: 1px solid var(--gray);
        padding: 20px;
        width: 100%;
        border-radius: 5px;
    }

    .developer-info.active{
        display: block;
    }
CSS;

    $this->registerCss($css); ?>
<?php endif;