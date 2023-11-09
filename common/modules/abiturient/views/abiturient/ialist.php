<?php

use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\models\User;
use common\modules\abiturient\assets\individualAchievementAsset\IndividualAchievementListAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\services\NextStepService;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;












$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/bachelor/individual-achievement/all',
    'Заголовок страницы ИД: `Личный кабинет поступающего | Индивидуальные достижения`'
);

IndividualAchievementListAsset::register($this);

?>

<?= $this->render('_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]); ?>

<div class="row">
    <div class="col-12">
        <?php $alert = Yii::$app->session->getFlash('errorSaveIA');
        if ($alert) {
            echo Html::tag('div', $alert, ['class' => 'alert alert-danger', 'role' => 'alert']);
        } ?>

        <?php if (
            $hasError &&
            $indachSaveError = Yii::$app->configurationManager->getText('indach_save_error', $application->type ?? null)
        ) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $indachSaveError; ?>
            </div>
        <?php endif; ?>

        <?php if ($indachTopText = Yii::$app->configurationManager->getText('indach_top_text', $application->type ?? null)) : ?>
            <div class="alert alert-info" role="alert">
                <?= $indachTopText; ?>
            </div>
        <?php endif; ?>

        <?php $receivingErrors = Yii::$app->session->getFlash('receivingIAErrors');
        if ($receivingErrors) : ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($receivingErrors as $key => $errors) : ?>
                    <p>
                        <?= Yii::t(
                            'abiturient/bachelor/individual-achievement/all',
                            'Тело сообщения об ошибке при обновлении ИД из 1С; на странице ИД: `Возникли ошибки валидации при получении индивидуального достижения (<strong>{key}</strong>) из 1С:`',
                            ['key' => $key]
                        ) ?>
                    </p>

                    <br>

                    <?php foreach ($errors as $error) : ?>
                        <ul class="ul-error-indent">
                            <?php foreach ($error as $error_text) : ?>
                                <li>
                                    <p>
                                        <?= $error_text ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $this->render(
            'ialist_partial/_ialist_panel',
            [
                'canEdit' => $canEdit,
                'application' => $application,
                'individualAchievementsDataProvider' => $ind_achs,
                'applicationComparisonWithActual' => $application_comparison,
            ]
        ) ?>
        <?php
        $next_step_service = new NextStepService($application);

        if ($next_step_service->getUseNextStepForwarding()) {
            $message = Yii::t(
                'abiturient/bachelor/individual-achievement/all',
                'Подпись кнопки перехода к следующему шагу; на странице индивидуальных достижений: `Перейти к следующему шагу`'
            );

            $next_step = $next_step_service->getNextStep('ia-list');
            if ($next_step !== 'ia-list') {
                echo Html::a(
                    $message,
                    $next_step_service->getUrlByStep($next_step),
                    ['class' => 'btn btn-primary float-right']
                );
            }
        }
        ?>
    </div>
</div>

<?php if ($indachBottomText = Yii::$app->configurationManager->getText('indach_bottom_text', $application->type ?? null)) : ?>
    <div class="alert alert-info" role="alert">
        <?= $indachBottomText; ?>
    </div>
<?php endif;