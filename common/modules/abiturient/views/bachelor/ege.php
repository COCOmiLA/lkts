<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\views\bachelor\assets\BachelorEgeAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








BachelorEgeAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/bachelor/ege/all',
    'Заголовок страницы ВИ: `Личный кабинет поступающего | Результаты экзаменов`'
);

?>

<?= $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]);
$exams_comparison_helper = null;
$exams_difference = null;
$exams_class = null;
if (isset($application_comparison) && $application_comparison) {
    $exams_comparison_helper = new ComparisonHelper($application_comparison, 'egeResults');
    [$exams_difference, $exams_class] = $exams_comparison_helper->getRenderedDifference();
}

$disabled = "";
$changeCounter = 0;
$isFileReadonly = false;
if (!$canEdit) {
    $disabled = 'disabled';
    $isFileReadonly = true;
}

$alert = Yii::$app->session->getFlash('examSaved');
if (strlen((string)$alert)) {
    echo Html::tag(
        'div',
        $alert,
        ['class' => 'alert alert-success', 'role' => 'alert']
    );
} ?>

<?php if ($examTopText = Yii::$app->configurationManager->getText('exam_top_text', $application->type ?? null)) : ?>
    <div class="alert alert-info" role="alert">
        <?= $examTopText; ?>
    </div>
<?php endif; ?>

<?php if (!$application->egeDisabled) : ?>
    <div class="row">
        <div class="col-12 ege-container">
            <?php $successEgeReload = Yii::$app->session->getFlash('successEgeReload');
            if ($successEgeReload === true) : ?>
                <div class="alert alert-success">
                    <p>
                        <?= Yii::t(
                            'abiturient/bachelor/ege/all',
                            'Информационный алерт о том что ВИ были успешно перезаполнены; на стр. ВИ: `Вступительные испытания успешно перезаполнены`'
                        ) ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($successEgeReload === false) : ?>
                <div class="alert alert-danger">
                    <p>
                        <?= Yii::t(
                            'abiturient/bachelor/ege/all',
                            'Алерт предупреждающий о том что имеются дубли предметов в таблице с ВИ; на стр. ВИ: `Обнаружены дубли предметов с различными данными. Для повторяющихся предметов укажите одинаковые год, форму сдачи, баллы и повторно нажмите "Перезаполнить список вступительных испытаний".`'
                        ) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <?php if (
                        !ArrayHelper::getValue($application, 'user.userRef') &&
                        $application->status != BachelorApplication::STATUS_CREATED &&
                        $loadFrom_1cInfo = Yii::$app->configurationManager->getText('load_from_1c_info', $application->type ?? null)
                    ) : ?>
                        <div class="alert alert-info update-info" role="alert">
                            <?= $loadFrom_1cInfo; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($application->haveEgeConflicts()) : ?>
                        <?php $reloadEgeUrl = Url::toRoute(["bachelor/reload-ege", 'id' => $application->id]); ?>
                        <a href="<?= $reloadEgeUrl ?>" class="btn btn-primary blue-button float-right">
                            <?= Yii::t(
                                'abiturient/bachelor/ege/all',
                                'Подпись кнопки перезаполнения ВИ; на стр. ВИ: `Перезаполнить список вступительных испытаний`'
                            ) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-10">
                    <h2 class="margin_for_h2" id="bachelor_entrance_test_sets">
                        <?= Yii::t(
                            'abiturient/bachelor/ege/all',
                            'Заголовок таблицы с результатами ВИ; на стр. ВИ: `Наборы вступительных испытаний`'
                        ) ?>
                    </h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="tab-content bachelor-tab">
                        <?= $this->render(
                            '_competitiveGroupEntranceTests',
                            [
                                'id' => $application->id,
                                'results' => $results,
                                'newEgeResult' => $newEgeResult,
                                'disable' => $isFileReadonly,
                                'competitiveGroupEntranceTest' => $competitiveGroupEntranceTest,
                            ]
                        ) ?>
                    </div>
                </div>
            </div>

            <?php if ($results) : ?>
                <div class="row">
                    <div class="col-10">
                        <h2 class="margin_for_h2" id="bachelor_entrance_test_results">
                            <?php if ($application->haveUnstagedDisciplineResult()) {
                                $tooltipTitle = Yii::t(
                                    'abiturient/bachelor/ege/all',
                                    'Всплывающая подсказка у заголовка с результатами когда набор не сохранён; на стр. ВИ: `Результаты вступительных испытаний не сохранены`'
                                );
                                echo "<i class=\"fa fa-edit blue_tooltip\" data-toggle=\"tooltip\" title=\"{$tooltipTitle}\"></i>";
                            } else {
                                $tooltipTitle = Yii::t(
                                    'abiturient/bachelor/ege/all',
                                    'Всплывающая подсказка у заголовка с результатами когда набор сохранён; на стр. ВИ: `Результаты вступительных испытаний сохранены`'
                                );
                                echo "<i class=\"fa fa-check green_tooltip\" data-toggle=\"tooltip\" title=\"{$tooltipTitle}\"></i>";
                            } ?>

                            <?= Yii::t(
                                'abiturient/bachelor/ege/all',
                                'Заголовок таблицы с результатами ВИ; на стр. ВИ: `Результаты вступительных испытаний`'
                            ) ?>
                            <?= $exams_difference ?: '' ?>
                        </h2>

                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="tab-content bachelor-tab">
                            <?= $this->render(
                                '_egeResult',
                                [
                                    'egeResults' => $results,
                                    'disable' => $isFileReadonly,
                                    'application' => $application,
                                    'attachments' => $attachments,
                                    'regulations' => $regulations,
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif;
