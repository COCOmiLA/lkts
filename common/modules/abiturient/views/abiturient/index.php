<?php

use yii\bootstrap4\Alert;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/main-page',
    'Заголовок страницы ЛК поступающего: `Личный кабинет поступающего`'
);
$user = Yii::$app->user->identity;

$alertMessage = Yii::t(
    'abiturient/main-page',
    'Сообщение об ошибке установки часового пояса страницы ЛК поступающего: `<strong>В портале не установлен часовой пояс.</strong> Вы не сможете работать с порталом до того, как проблема будет решена. Приносим извинения за неудобства. Обратитесь к администратору портала.`'
);

?>

<?php if ($timeZoneError) : ?>
    <?= Html::tag(
        'div',
        $alertMessage,
        [
            'class' => 'alert alert-danger',
            'role' => 'alert'
        ]
    ); ?>
<?php else : ?>
    <?= $this->render(
        '_abiturientheader',
        ['route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0]]
    ); ?>

    <?php if ($canCreateQuestionary) : ?>
        <div class="row abiturient-body">
            <?php if ($indexTopText = Yii::$app->configurationManager->getText('index_top_text')) : ?>
                <div class="alert alert-info" role="alert">
                    <?= $indexTopText; ?>
                </div>

                <div style="clear:both;"></div>
            <?php endif; ?>

            <div class="col-12 col-md-8">
                <?= $this->render('instructions/_instruction'); ?>
            </div>

            <div class="col-12 col-md-4">
                <?php if ($user->canMakeStep('make-application')) : ?>
                    <a href="#" class="btn btn-outline-secondary btn-lg btn-block py-3 blue-button" id="make-appication" data-toggle="modal" data-target="#myModal">
                        <?= $alertMessage = Yii::t(
                            'abiturient/main-page',
                            'Подпись ссылки переводящей на форму заполнения заявления на страницы ЛК поступающего: `Подать заявление`'
                        ); ?>
                    </a>
                <?php elseif ($user->canMakeStep('questionary')) : ?>
                    <a href="<?= Url::toRoute('abiturient/questionary') ?>" class="btn btn-outline-secondary btn-lg btn-block py-3 blue-button" id="make-questionary">
                        <?= $alertMessage = Yii::t(
                            'abiturient/main-page',
                            'Подпись ссылки переводящей на форму заполнения анкеты на страницы ЛК поступающего: `Заполнить анкету`'
                        ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if ($indexBottomText = Yii::$app->configurationManager->getText('index_bottom_text')) : ?>
                    <div class="alert alert-info" role="alert">
                        <?= $indexBottomText; ?>
                    </div>

                    <div style="clear:both;"></div>
                <?php endif; ?>
            </div>
        </div>
    <?php else : ?>
        <?php $liString = '';
        foreach ($campaignStartDates as $campaignStart) {
            $campaignStart['minDate'] = date('Y.m.d', strtotime($campaignStart['minDate']));
            $liText = Yii::t(
                'abiturient/main-page',
                'Элемент списка отображающий название ПК и дату её начала на страницы ЛК поступающего: `Дата начала приема документов "{nameCampaign}" - {minDate}`',
                [
                    'minDate' => $campaignStart['minDate'],
                    'nameCampaign' => $campaignStart['nameCampaign'],
                ]
            );
            $liString .= Html::tag('li', $liText);
        }
        echo Alert::widget([
            'body' => Yii::t(
                'abiturient/main-page',
                'Шаблон тела алерта об отсутствие доступных ПК на страницы ЛК поступающего: `Подача заявлений временно невозможна.{ui}`',
                ['ui' => Html::tag('ui', $liString)]
            ),
            'options' => ['class' => 'alert-danger'],
        ]) ?>
    <?php endif; ?>
<?php endif;