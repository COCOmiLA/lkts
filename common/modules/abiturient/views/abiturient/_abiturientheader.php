<?php

use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\models\settings\ChatSettings;
use common\models\settings\SandboxSetting;
use common\modules\abiturient\assets\abiturientHeaderAsset\AbiturientHeaderAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\widgets\ApplicationValidationWidget;
use common\widgets\TooltipWidget\TooltipWidget;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$current_user = Yii::$app->user->identity;
if (!isset($current_application)) {
    $current_application = null;
    $app_id = Yii::$app->request->get("id");
    if ($app_id) {
        $current_application = BachelorApplication::findOne($app_id);
    }
}

$unsaved_leave = Yii::$app->configurationManager->getText('unsaved_leave', isset($current_application) ? $current_application->type ?? null : null);
$this->registerJsVar('unsavedLeave', $unsaved_leave);
AbiturientHeaderAsset::register($this);

$sandbox_enabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);
$available_types = $current_user->availableApplicationTypes;

?>

<div class="row">
    <div class="col-12 mb-3">
        <ul class="nav nav-pills d-flex justify-content-end flex-wrap">
            <?php if (ChatSettings::getValueByName(ChatSettings::ENABLE_CHAT)) : ?>
                <li role="presentation" class="nav-item">
                    <a class="nav-link" href="<?= Url::toRoute(['chat/entrant-index']); ?>">
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Подпись ссылки перехода к странице чата на панели навигации ЛК: `Чат с приёмной комиссией`'
                        ); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($current_user->canViewStep('questionary')) : ?>
                <?php $navLinkClass = 'nav-link';
                if ($route == "abiturient/questionary") {
                    $navLinkClass .= ' active';
                } ?>
                <li role="presentation" class="nav-item">
                    <?php $quesionary_url = ['abiturient/questionary'];
                    if (isset($current_application)) {
                        $quesionary_url['app_id'] = $current_application->id;
                    } elseif (isset($application_id_for_questionary)) {
                        $quesionary_url['app_id'] = $application_id_for_questionary;
                    } ?>
                    <a class="<?= $navLinkClass ?>" href="<?= Url::toRoute($quesionary_url); ?>">
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Подпись ссылки перехода к странице анкеты на панели навигации ЛК: `Анкета`'
                        ); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php $applications = $current_user->getApplications()
                ->andWhere(['not', [BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_MODERATING]])
                ->all(); ?>
            <?php if ($current_user->canViewStep('make-application') && !$applications) : ?>
                <?php $navLinkClass = 'nav-link';
                $can_make_load_scans = $current_user->canMakeStep('load-scans', $current_application);
                if (!$current_user->canMakeStep('make-application', $current_application)) {
                    $navLinkClass .= ' disabled';
                } ?>
                <li role="presentation" class="nav-item">
                    <a class="<?= $navLinkClass ?>" href="#" data-toggle="modal" data-target="#myModal">
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Подпись кнопки создания нового заявления на панели навигации ЛК: `Создать заявление`'
                        ); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($current_user->canViewStep('my-applications')) : ?>
                <?php $groupped_apps = ArrayHelper::index(
                    $applications,
                    null,
                    function ($app) {
                        return $app->type->name;
                    }
                ); ?>
                <li role="presentation" class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <?php $linkText = Yii::t(
                            'abiturient/header/all',
                            'Подпись ссылки перехода к странице просмотра всех заявлений на панели навигации ЛК: `Мои заявления`'
                        ); ?>
                        <?= isset($current_application) ? $current_application->type->name : $linkText ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-with-old-style">
                        <?php foreach ($groupped_apps as $type_name => $apps) : ?>
                            <?php if (!is_array($apps)) {
                                $apps = [$apps];
                            } ?>
                            <?php foreach ($apps as $application) : ?>
                                <li>
                                    <a href="<?= Url::toRoute(['bachelor/education', 'id' => $application->id,]); ?>">
                                        <?= $type_name; ?> (<?= $application->translateStatus() ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>

                            <li role="separator" class="dropdown-divider"></li>
                        <?php endforeach; ?>

                        <li class="dropdown-divider" role="separator"></li>

                        <?php if ($available_types) : ?>
                            <li>
                                <a class="semibold-link" href="#" data-toggle="modal" data-target="#myModal">
                                    <?= Yii::t(
                                        'abiturient/header/all',
                                        'Подпись кнопки создания нового заявления на панели навигации ЛК: `Новое заявление`'
                                    ); ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li>
                            <a class="semibold-link" href="<?= Url::toRoute(['abiturient/applications']); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице просмотра всех заявлений на панели навигации ЛК: `Все заявления`'
                                ); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (isset($current_application)) : ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-with-old-style">
                        <li class="dropdown-header">
                            <?= Yii::t(
                                'abiturient/header/all',
                                'Подпись кнопки выпадающего списка с заявлениями на панели навигации ЛК: `Заявление:`'
                            ); ?>
                        </li>

                        <li>
                            <?= $this->render(
                                '@abiturient/views/partial/changeHistoryModal/_changeHistoryModalButton',
                                [
                                    'button' => Html::a(
                                        Yii::t(
                                            'abiturient/change-history',
                                            'Подпись кнопки истории изменений на панели навигации ЛК: `История изменений`'
                                        ),
                                        '#',
                                        [
                                            'data-toggle' => 'modal',
                                            'id' => 'changeHistoryModalButton',
                                            'data-target' => '#changeHistoryModal',
                                        ]
                                    ),
                                    'application' => $current_application
                                ]
                            ) ?>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <?php if (!$available_types && !CampaignInfo::getActiveCampaignsQuery()->exists()) : ?>
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <?php echo Yii::t(
                    'abiturient/header/all',
                    'Текст о невозможности создания заявления на панели навигации ЛК: `Создание заявления в текущий момент невозможно, так как не обнаружено активных этапов приёмных кампаний, обратитесь в приёмную кампанию.`'
                ); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (
        isset($current_application) &&
        !$current_application->isDraftInSendMode() &&
        $current_application->isIn1C()
    ) : ?>
        <div class="col-12 mb-3">
            <div class="row">
                <div class="col-md-3 col-md-offset-9 col-12">
                    <div class="d-flex justify-content-center align-items-center">
                        <?php echo Html::a(
                            Yii::t(
                                'abiturient/header/all',
                                'Подпись ссылки актуализации заявления из Информационной системы вуза на панели навигации ЛК: `Актуализировать заявление из ПК`'
                            ),
                            [
                                '/bachelor/update-full-package',
                                'id' => $current_application->id,
                                'baseUrl' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0]
                            ],
                            [
                                'class' => 'btn btn-primary',
                                'title' => Yii::t(
                                    'abiturient/header/all',
                                    'Содержание всплывающей подсказки для кнопки актуализации заявления из Информационной системы вуза на панели навигации ЛК: `Обновить анкету и заявление из ПК`'
                                ),
                                'data' => [
                                    'toggle' => 'tooltip',
                                    'placement' => 'bottom',
                                ]
                            ]
                        );
                        echo TooltipWidget::widget([
                            'message' => Yii::$app->configurationManager->getText('update_application_tooltip'),
                            'params' => 'style="margin-left: 4px;"'
                        ])
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->render('@abiturient/views/partial/changeHistoryModal/_changeHistoryModal') ?>

    <?php if (isset($current_application)) : ?>
        <?php
        $can_view_ege_result = $current_user->canViewStep('ege-result', $current_application);
        $can_view_education = $current_user->canViewStep('education', $current_application);
        $can_view_specialities = $current_user->canViewStep('specialities', $current_application); ?>

        <?php if ($can_view_ege_result || $can_view_education || $can_view_specialities) : ?>
            <div class="col-12 mb-3">
                <ul class="nav nav-pills nav-fill abit-header mb-n2 ml-n2">
                    <?php if ($can_view_education) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        if ($route == "bachelor/education") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$current_user->canMakeStep('education', $current_application)) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/education', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице образования в заявлении на панели навигации ЛК: `Образование`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($current_user->canViewStep('accounting-benefits', $current_application)) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        if ($route == "bachelor/accounting-benefits") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$current_user->canMakeStep('accounting-benefits', $current_application)) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/accounting-benefits', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице льгот в заявлении на панели навигации ЛК: `Особые условия поступления`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($can_view_specialities) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        $can_make_specialities = $current_user->canMakeStep('specialities', $current_application);
                        if ($route == "bachelor/application") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$can_make_specialities) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <?php $url = Url::toRoute([
                                ($can_make_specialities) ? 'bachelor/application' : 'bachelor/ege',
                                'id' => $current_application->id
                            ]); ?>
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= $url ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице с НП в заявлении на панели навигации ЛК: `Направления подготовки`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($can_view_ege_result) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        if ($route == "bachelor/ege") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$current_user->canMakeStep('ege-result', $current_application)) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/ege', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице с ВИ в заявлении на панели навигации ЛК: `Вступительные испытания`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($current_user->canViewStep('ia-list', $current_application)) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        if ($route == "abiturient/ialist") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$current_user->canMakeStep('ia-list', $current_application)) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['abiturient/ialist', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице с ИД в заявлении на панели навигации ЛК: `Индивидуальные достижения`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($current_user->canViewStep('load-scans', $current_application)) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        $can_make_load_scans = $current_user->canMakeStep('load-scans', $current_application);
                        if ($route == "bachelor/load-scans") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$can_make_load_scans) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" style="position:relative" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/load-scans', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице со сканами в заявлении на панели навигации ЛК: `Сканы документов`'
                                ); ?>
                            </a>

                            <?php if (!$current_application->isNotCreatedDraft() && !$current_application->isRequiredCommonFilesAttached() && $can_make_load_scans) : ?>
                                <?php $tooltipTitle = Yii::t(
                                    'abiturient/header/all',
                                    'Содержание всплывающей подсказки для информирующей иконке на кнопке "Сканы документов" на панели навигации ЛК: `Необходимо прикрепить скан-копии`'
                                ) ?>
                                <span class="badge badge-pill" style="position: absolute;top: -5px;right: -5px;padding: 9px 9px;background: var(--yellow);border-radius: 100%;" data-toggle="tooltip" data-placement="top" title="<?= $tooltipTitle; ?>">
                                    <i style="color: tomato;font-size: 20px;margin-top: -3px;margin-right: -2px;" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>

                    <?php if ($current_user->canViewStep('make-comment', $current_application)) : ?>
                        <?php $navLinkClass = 'btn btn-outline-primary';
                        if ($route == "bachelor/comment") {
                            $navLinkClass = 'nav-link active';
                        }
                        if (!$current_user->canMakeStep('make-comment', $current_application)) {
                            $navLinkClass .= ' disabled';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/comment', 'id' => $current_application->id]); ?>">
                                <?= Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки перехода к странице с комментариями в заявлении на панели навигации ЛК: `Комментарий`'
                                ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($current_user->canViewStep('send-application', $current_application)) : ?>
                        <?php $navLinkClass = 'btn';
                        if ($route == "bachelor/send-application") {
                            $navLinkClass = 'btn active';
                        }
                        if (!$current_user->canMakeStep('send-application', $current_application)) {
                            $navLinkClass .= ' btn-outline-secondary disabled';
                        } else {
                            $navLinkClass .= ' btn-outline-success';
                        } ?>
                        <li role="presentation" class="nav-item mb-2 ml-2">
                            <a class="py-3 justify-content-center align-items-center <?= $navLinkClass ?>" href="<?= Url::toRoute(['bachelor/send-application', 'id' => $current_application->id]); ?>">
                                <?= $current_application->getApplyText(); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <?php $resultOfCheckingAllApplication = Yii::$app->session->getFlash('resultOfCheckingAllApplication'); ?>
            <?php if (!empty($resultOfCheckingAllApplication)) : ?>
                <div class="alert alert-danger">
                    <?= Yii::t(
                        'abiturient/header/all',
                        'Текст ошибки о невозможности подачи заявления на панели навигации ЛК: `<strong>Внимание!</strong> Не все обязательные данные внесены.`'
                    ); ?>

                    <ul style="margin-left: 20px">
                        <?php foreach ($resultOfCheckingAllApplication as $result) {
                            echo Html::tag(
                                'li',
                                Html::a($result['title'], $result['url']) . $result['message']
                            );
                        } ?>
                    </ul>
                </div>
            <?php elseif ($current_application) : ?>
                <?php echo ApplicationValidationWidget::widget([
                    'app' => $current_application
                ]) ?>
            <?php endif; ?>

            <?php $checkEgeErrorsAbit = Yii::$app->session->getFlash('checkEgeErrorsAbit');
            $checkEgeErrorsAbit = json_decode((string)$checkEgeErrorsAbit); ?>
            <?php if ($checkEgeErrorsAbit) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($checkEgeErrorsAbit as $add_error) : ?>
                        <div>
                            <strong>
                                <?= $add_error->name; ?>
                            </strong>

                            <ul style="margin-left: 20px">
                                <?php foreach ($add_error->errors as $error) : ?>
                                    <li>
                                        <?= $error; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php $regulationError = Yii::$app->session->getFlash('regulationError'); ?>
            <?php if (!empty($regulationError)) : ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php echo $regulationError; ?>
                </div>
            <?php endif; ?>

            <?php $applicationHasError = Yii::$app->session->getFlash('applicationHasError');
            $agreeError = Yii::$app->session->get('agreement_error');
            if (empty($resultOfCheackingAllApplication)) {
                echo $this->render('./../bachelor/_applicationStatusMessage', [
                    'hasError' => $applicationHasError,
                    'application' => $current_application,
                    'agreeError' => $agreeError == '1',
                ]);
            } ?>

            <?php if ($current_application->getOrderInfo()) : ?>
                <div class="alert alert-success" role="alert">
                    <p>
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Текст сообщения о зачислении поступающего с номером приказа на панели навигации ЛК: `Вы зачислены. Информация о приказе: {orderInfo}`',
                            ['orderInfo' => $current_application->getOrderInfo()]
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (
                ($current_application->type->blocked || $current_application->type->stageTwoStarted()) &&
                $blockTopText = Yii::$app->configurationManager->getText('block_top_text', $application->type ?? null)
            ) : ?>
                <div class="alert alert-info" role="alert">
                    <?= $blockTopText; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php $actual_app = DraftsManager::getApplicationDraftByOtherDraft($current_application, IDraftable::DRAFT_STATUS_APPROVED);
        $sent_app = DraftsManager::getApplicationDraftByOtherDraft($current_application, IDraftable::DRAFT_STATUS_SENT); ?>
        <?php if ($actual_app || $sent_app) : ?>
            <?php $currently_has_draft = $current_application->hasCreatedDraft(); ?>
            <div class="col-12">
                <div class="row">
                    <?php if ($actual_app) : ?>
                        <?php if ($actual_app->id != $current_application->id) : ?>
                            <div class="col-md-6 col-sm-12">
                                <?php $data = ['class' => 'btn btn-success btn-sm btn-block mb-3'];
                                $url = Url::toRoute(['bachelor/application', 'id' => $actual_app->id]);
                                $faIcon = '<i class="fa fa-eye" aria-hidden="true"></i>';
                                $btnLabel = "{$faIcon} " . Yii::t(
                                    'abiturient/header/modal-creation-draft-from-approved-application',
                                    'Подпись ссылки на принятое ранее заявление; на панели навигации ЛК: `Просмотреть принятое заявление`'
                                );
                                $tooltipWidget = TooltipWidget::widget([
                                    'message' => Yii::$app->configurationManager->getText('tooltip_for_view_accepted_statement'),
                                    'params' => 'style="margin-left: 4px;"'
                                ]);
                                echo Html::a($btnLabel . $tooltipWidget, $url, $data); ?>
                            </div>
                        <?php endif; ?>
                        <?php
                        $faIcon = '<i class="fa fa-files-o" aria-hidden="true"></i>';

                        $openModalBtnLabel = "{$faIcon} " . Yii::t(
                            'abiturient/header/modal-creation-draft-from-approved-application',
                            'Подпись кнопки открытия модального окна создания черновика из принятого заявления; на панели навигации ЛК: `Сделать черновик из принятого заявления`'
                        ) . TooltipWidget::widget([
                            'message' => Yii::$app->configurationManager->getText('tooltip_for_make_a_draft_from_the_accepted_statement'),
                            'params' => 'style="margin-left: 4px;"'
                        ]); ?>

                        <?php if (
                            $current_application->draft_status != IDraftable::DRAFT_STATUS_CREATED
                            && ($current_application->type->checkResubmitPermission($current_application->user) || !$current_application->hasApprovedApplication())
                        ) : ?>
                            <div class="col-md-6 col-sm-12">
                                <?php $data = ['class' => 'btn btn-info btn-sm btn-block mb-3'];
                                $url = Url::toRoute(['bachelor/make-copy', 'id' => $actual_app->id]);
                                ?>
                                <?php if ($currently_has_draft) : ?>
                                    <?php $cancelBtn = Html::button(
                                        Yii::t(
                                            'abiturient/header/modal-creation-draft-from-approved-application',
                                            'Подпись кнопки отмены; в модальном окне создания черновика из принятого заявления на панели навигации ЛК: `Отмена`'
                                        ),
                                        [
                                            'data-dismiss' => 'modal',
                                            'class' => 'btn btn-outline-secondary',
                                        ]
                                    );
                                    $confirmBtn = Html::a(
                                        Yii::t(
                                            'abiturient/header/modal-creation-draft-from-approved-application',
                                            'Подпись кнопки подтверждения; в модальном окне создания черновика из принятого заявления на панели навигации ЛК: `Подтверждаю`'
                                        ),
                                        $url,
                                        ['class' => 'btn btn-primary']
                                    ); ?>
                                    <?php Modal::begin([
                                        'title' => Html::tag(
                                            'h4',
                                            Yii::t(
                                                'abiturient/header/modal-creation-draft-from-approved-application',
                                                'Заголовок в модальном окне создания черновика из принятого заявления на панели навигации ЛК: `Подтвердите действие`'
                                            )
                                        ),
                                        'toggleButton' => ArrayHelper::merge([
                                            'tag' => 'button',
                                            'label' => $openModalBtnLabel
                                        ], $data),
                                        'footer' => $cancelBtn . $confirmBtn
                                    ]); ?>
                                    <p>
                                        <?= Yii::t(
                                            'abiturient/header/modal-creation-draft-from-approved-application',
                                            'Текст сообщения; в модальном окне создания черновика из принятого заявления на панели навигации ЛК: `Вы уверены, что хотите создать черновик на основе принятого заявления? Это действие перезапишет уже существующий черновик`'
                                        ) ?>
                                    </p>
                                    <?php
                                    Modal::end(); ?>
                                <?php else : ?>
                                    <?= Html::a($openModalBtnLabel, $url, $data); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($sent_app) : ?>
                        <?php if ($sent_app->id != $current_application->id) : ?>
                            <div class="col-md-6 col-sm-12">
                                <?php $data = ['class' => 'btn btn-success btn-sm btn-block mb-3'];
                                $url = Url::toRoute(['bachelor/application', 'id' => $sent_app->id]);
                                $faIcon = '<i class="fa fa-eye" aria-hidden="true"></i>';
                                $btnLabel = "{$faIcon} " . Yii::t(
                                    'abiturient/header/all',
                                    'Подпись ссылки на заявление находящиеся на проверке; на панели навигации ЛК: `Просмотреть заявление на проверке`'
                                );
                                $tooltipWidget = TooltipWidget::widget([
                                    'message' => Yii::$app->configurationManager->getText('tooltip_for_view_an_inspection_application'),
                                    'params' => 'style="margin-left: 4px;"'
                                ]); ?>
                                <?= Html::a($btnLabel . $tooltipWidget, $url, $data); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($current_application->draft_status != IDraftable::DRAFT_STATUS_CREATED) : ?>
                            <?php if (
                                $current_application->type->checkResubmitPermission($current_application->user) || !$current_application->hasApprovedApplication()
                            ) : ?>
                                <div class="col-md-6 col-sm-12">
                                    <?php $data = ['class' => 'btn btn-info btn-sm btn-block mb-3'];
                                    $url = Url::toRoute(['bachelor/make-copy', 'id' => $sent_app->id]);
                                    $faIcon = '<i class="fa fa-files-o" aria-hidden="true"></i>';
                                    $openModalBtnLabel = "{$faIcon} " . Yii::t(
                                            'abiturient/header/modal-creation-draft-from-previously-sent-application',
                                            'Подпись кнопки открытия модального окна создания черновика из ранее отправленного заявления; на панели навигации ЛК: `Сделать черновик из ранее отправленного заявления`'
                                        ) . TooltipWidget::widget([
                                            'message' => Yii::$app->configurationManager->getText('tooltip_for_make_a_draft_from_the_previously_sent_statement'),
                                            'params' => 'style="margin-left: 4px;"'
                                        ]); ?>
                                    <?php if ($currently_has_draft) : ?>
                                        <?php $cancelBtn = Html::button(
                                            Yii::t(
                                                'abiturient/header/modal-creation-draft-from-previously-sent-application',
                                                'Подпись кнопки отмены; в модальном окне создания черновика из ранее отправленного заявления на панели навигации ЛК: `Отмена`'
                                            ),
                                            [
                                                'data-dismiss' => 'modal',
                                                'class' => 'btn btn-outline-secondary',
                                            ]
                                        );
                                        $confirmBtn = Html::a(
                                            Yii::t(
                                                'abiturient/header/modal-creation-draft-from-previously-sent-application',
                                                'Подпись кнопки подтверждения; в модальном окне создания черновика из ранее отправленного заявления на панели навигации ЛК: `Подтверждаю`'
                                            ),
                                            $url,
                                            ['class' => 'btn btn-primary']
                                        ); ?>
                                        <?php Modal::begin([
                                            'title' => Html::tag(
                                                'h4',
                                                Yii::t(
                                                    'abiturient/header/modal-creation-draft-from-previously-sent-application',
                                                    'Заголовок в модальном окне создания черновика из ранее отправленного заявления на панели навигации ЛК: `Подтвердите действие`'
                                                )
                                            ),
                                            'toggleButton' => ArrayHelper::merge([
                                                'tag' => 'button',
                                                'label' => $openModalBtnLabel
                                            ], $data),
                                            'footer' => $cancelBtn . $confirmBtn
                                        ]); ?>
                                        <p>
                                            <?= Yii::t(
                                                'abiturient/header/modal-creation-draft-from-previously-sent-application',
                                                'Текст сообщения; в модальном окне создания черновика из ранее отправленного заявления на панели навигации ЛК: `Вы уверены, что хотите создать черновик на основе отправленного заявления? Это действие перезапишет уже существующий черновик`'
                                            ) ?>
                                        </p>
                                        <?php Modal::end(); ?>
                                    <?php else : ?>
                                        <?= Html::a($openModalBtnLabel, $url, $data); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($current_application->draft_status != IDraftable::DRAFT_STATUS_CREATED) : ?>
                        <?php if ($currently_has_draft): ?>
                            <div class="col-md-6 col-sm-12">
                                <?php
                                $data = ['class' => 'btn btn-success btn-sm btn-block mb-3'];
                                $created_app = DraftsManager::getApplicationDraftByOtherDraft($current_application, IDraftable::DRAFT_STATUS_CREATED);
                                $url = Url::toRoute(['bachelor/application', 'id' => $created_app->id]);
                                $faIcon = '<i class="fa fa-pencil" aria-hidden="true"></i>';
                                $btnLabel = "{$faIcon} " . Yii::t(
                                        'abiturient/header/all',
                                        'Подпись ссылки на заявление готовящееся к подаче; на панели навигации ЛК: `Перейти к готовящемуся заявлению`'
                                    );
                                $tooltipWidget = TooltipWidget::widget([
                                    'message' => Yii::$app->configurationManager->getText('tooltip_for_link_to_created_application'),
                                    'params' => 'style="margin-left: 4px;"'
                                ]); ?>
                                <?= Html::a($btnLabel . $tooltipWidget, $url, $data); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($available_types) : ?>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?= Html::beginForm(Url::toRoute('bachelor/create-application'), 'post', ['name' => 'create-app-form', 'id' => 'create-app-form']); ?>

                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Заголовок модального окна выбора ПК: `Выбор приемной кампании`'
                        ); ?>
                    </h4>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <?= Html::dropDownList(
                        'application_type',
                        Yii::$app->configurationManager->getCode('chosen_campaign_id_by_default'),
                        ArrayHelper::map($available_types, 'id', 'name'),
                        [
                            'class' => 'form-control',
                            'id' => 'education-form',
                            'prompt' => Yii::t(
                                'abiturient/header/all',
                                'Подпись пустого значения в выпадающем списке; модального окна выбора ПК: `Выберите ...`'
                            ),
                        ]
                    ); ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <?= Yii::t(
                            'abiturient/header/all',
                            'Подпись кнопки отмены в модальном окне выбора ПК: `Отмена`'
                        ); ?>
                    </button>

                    <?php $inputValue = Yii::t(
                        'abiturient/header/all',
                        'Подпись кнопки для сохранения формы в модальном окне выбора ПК: `Выбрать`'
                    ); ?>
                    <input id="submit" type="submit" name="submit" value="<?= $inputValue ?>" class="btn btn-primary">
                </div>

                <?= Html::endForm(); ?>
            </div>
        </div>
    </div>
<?php endif;