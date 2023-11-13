<?php

use common\models\settings\SandboxSetting;
use common\models\User;
use common\modules\abiturient\assets\abiturientQuestionaryAsset\ApplicationsAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\widgets\TooltipWidget\TooltipWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;







ApplicationsAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/applications/all',
    'Заголовок страницы заявлений поступающего: `Личный кабинет поступающего`'
);

$user = Yii::$app->user->identity;
$where_to_redirect = User::findOne(Yii::$app->user->identity->id);
$sandBoxEnabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);
$type_for_texts = \yii\helpers\ArrayHelper::getValue($applications ?? null, '0.type');

echo $this->render(
    '_abiturientheader',
    ['route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0]]
); ?>

<div class="row">
    <div class="col-12">
        <?php if (
            $user->haveBlockedTypes() &&
            $blockTopText = Yii::$app->configurationManager->getText('block_top_text', $type_for_texts)
        ) : ?>
            <div class="alert alert-info" role="alert">
                <?php echo $blockTopText; ?>
            </div>
        <?php endif; ?>

        <?php if ($applicationsTopText = Yii::$app->configurationManager->getText('applications_top_text', $type_for_texts)) : ?>
            <div class="alert alert-info" role="alert">
                <?php echo $applicationsTopText; ?>
            </div>

            <div class="clear-both"></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12 col-md-6">
                <h3>
                    <?= Yii::t(
                        'abiturient/applications/all',
                        'Заголовок панели на странице заявлений поступающего: `Поданные заявления`'
                    ); ?>
                </h3>
            </div>

            <?php if ($user->canViewStep('make-application') && $user->canMakeStep('make-application')) : ?>
                <div class="col-12 col-md-6">
                    <a href="#" class="btn btn-primary float-right make-app btn-make-modal" id="make-appication" data-toggle="modal" data-target="#myModal">
                        <?= Yii::t(
                            'abiturient/main-page',
                            'Подпись ссылки переводящей на форму заполнения заявления на страницы ЛК поступающего: `Подать заявление`'
                        ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table valign-middle">
                <tr>
                    <th>
                        <?= Yii::t(
                            'abiturient/applications/application-table',
                            'Подпись колонки "Уровень образования" таблицы с заявлениями на странице заявлений поступающего: `Уровень образования`'
                        ); ?>
                    </th>

                    <th>
                        <?= Yii::t(
                            'abiturient/applications/application-table',
                            'Подпись колонки "Дата подачи" таблицы с заявлениями на странице заявлений поступающего: `Дата подачи`'
                        ); ?>
                    </th>

                    <th>
                        <?= Yii::t(
                            'abiturient/applications/application-table',
                            'Подпись колонки "Статус" таблицы с заявлениями на странице заявлений поступающего: `Статус`'
                        ); ?>
                    </th>

                    <th>&nbsp;</th>
                </tr>

                <?php if (isset($applications) && $applications) : ?>
                    <?php foreach ($applications as $application) : ?>
                        <?php  ?>

                        <tr class="no-bottom">
                            <td>
                                <?php if ($application->getOrderInfo()) : ?>
                                    <?php $tooltipTitle = Yii::t(
                                        'abiturient/applications/application-table',
                                        'Текст всплывающей подсказки для пиктограммы о том что заявление принято в ПК; в таблице заявлений на странице заявлений поступающего: `Вы зачислены. Информация о приказе: {applicationOrderInfo}`',
                                        ['applicationOrderInfo' => $application->getOrderInfo()]
                                    ) ?>
                                    <i class="fa fa-check verfied order-check" aria-hidden="true" title="<?= $tooltipTitle; ?>"></i>
                                <?php endif; ?>

                                <?= $application->type->name; ?>:
                            </td>

                            <td class="width-20-percent">
                                <?php if (empty($application->sent_at)) : ?>
                                    &ndash;
                                <?php else : ?>
                                    <?= date('d.m.Y', $application->sent_at); ?>
                                <?php endif; ?>
                            </td>

                            <td class="width-20-percent word-breaker">
                                <?php if ($application->getOrderInfo() && !$application->status == BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED) : ?>
                                    <p>
                                        <?= Yii::t(
                                            'abiturient/applications/application-table',
                                            'Текст сообщения о том что заявление принято в ПК; в таблице заявлений на странице заявлений поступающего: `Вы зачислены. Информация о приказе: {applicationOrderInfo}`',
                                            ['applicationOrderInfo' => $application->getOrderInfo()]
                                        ) ?>
                                    </p>
                                <?php elseif (in_array($application->status, [BachelorApplication::STATUS_WANTS_TO_RETURN_ALL, BachelorApplication::STATUS_WANTS_TO_BE_REMOTE])) : ?>
                                    <i class="fa fa-ban" aria-hidden="true"></i>
                                    <?= Yii::t(
                                        'abiturient/applications/application-table',
                                        'Текст сообщения о том что заявление отозвано из ПК; в таблице заявлений на странице заявлений поступающего: `Отозвано`'
                                    ); ?>
                                <?php else : ?>
                                    <?= $application->translateStatus(); ?>
                                    <?= TooltipWidget::widget([
                                        'message' => Yii::$app->configurationManager->getText($application->tooltipNameByStatus, $application->type ?? null)
                                    ]) ?>
                                <?php endif; ?>
                            </td>

                            <td class="controls-td width-15-percent">
                                <?php if ($application->draft_status == IDraftable::DRAFT_STATUS_CREATED) : ?>
                                    <?php if (!$application->canEdit()) : ?>
                                        <?php $url = Url::toRoute([
                                            'bachelor/application',
                                            'id' => $application->id
                                        ]) ?>
                                        <a href="<?= $url; ?>" class="btn btn-outline-secondary btn-applications">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                            <?= Yii::t(
                                                'abiturient/applications/application-table',
                                                'Подпись ссылки переадресующей к заявлению, которое заблокировано; в таблице заявлений на странице заявлений поступающего: `Просмотреть`'
                                            ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php $url = Url::toRoute([
                                            ($where_to_redirect->canMakeStep('specialities')) ? 'bachelor/application' : 'bachelor/education',
                                            'id' => $application->id
                                        ]) ?>
                                        <a href="<?= $url; ?>" class="btn btn-outline-secondary btn-applications">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                            <?= Yii::t(
                                                'abiturient/applications/application-table',
                                                'Подпись ссылки переадресующей к заявлению, которое не заблокировано; в таблице заявлений на странице заявлений поступающего: `Редактировать`'
                                            ); ?>
                                        </a>

                                        <?= Html::beginForm(Url::toRoute('abiturient/remove-application'), 'POST', ['name' => 'remove-app', 'id' => 'remove-app' . $application->id]); ?>

                                        <?= Html::hiddenInput("appid", $application->id); ?>

                                        <?php $data = [];
                                        if ($application->haveNotDeleteRevertableSpec()) {
                                            $data = ['confirm' => Yii::t(
                                                'abiturient/applications/application-table',
                                                'Текст подтверждения при попытке удаления заявления; в таблице заявлений на странице заявлений поступающего: `Вы уверены, что хотите удалить заявление? Вы не сможете отменить данное действие. В вашем заявлении есть направления подготовки, прием заявлений по которым прекращен.`'
                                            )];
                                        } ?>
                                        <?php $deleteBtnLabel = Yii::t(
                                            'abiturient/applications/application-table',
                                            'Подпись кнопки удаления заявления; в таблице заявлений на странице заявлений поступающего: `Удалить`'
                                        ); ?>
                                        <?= Html::submitButton(
                                            "<i class='fa fa-remove' aria-hidden='true'></i> {$deleteBtnLabel}",
                                            [
                                                'class' => 'btn btn-outline-secondary btn-block',
                                                'data' => $data
                                            ]
                                        ); ?>

                                        <?= Html::endForm(); ?>
                                    <?php endif; ?>
                                <?php elseif ($application->draft_status == IDraftable::DRAFT_STATUS_SENT) : ?>
                                    <?php $url = Url::toRoute([
                                        'bachelor/application',
                                        'id' => $application->id
                                    ]); ?>
                                    <a href="<?= $url ?>" class="btn btn-outline-secondary btn-applications">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                        <?= Yii::t(
                                            'abiturient/applications/application-table',
                                            'Подпись ссылки переадресующей к заявлению, которое заблокировано; в таблице заявлений на странице заявлений поступающего: `Просмотреть`'
                                        ); ?>
                                    </a>

                                    <?php if (
                                        !$application->moderatingNow()
                                        && ($application->status == BachelorApplication::STATUS_WANTS_TO_RETURN_ALL
                                            || $application->type->allow_remove_sent_application_after_moderation
                                            || !$application->hasDeclinedRecordInHistory()
                                        )
                                    ) : ?>
                                        <?= Html::beginForm(Url::toRoute('abiturient/remove-application'), 'POST', ['name' => 'remove-app', 'id' => 'remove-app' . $application->id]); ?>
                                        <?= Html::hiddenInput("appid", $application->id); ?>
                                        <?php $data = [];
                                        if (!in_array($application->status, [BachelorApplication::STATUS_WANTS_TO_RETURN_ALL, BachelorApplication::STATUS_WANTS_TO_BE_REMOTE]) && $application->haveNotDeleteRevertableSpec()) {
                                            $data = ['confirm' => Yii::t(
                                                'abiturient/applications/application-table',
                                                'Текст подтверждения при попытке удаления заявления; в таблице заявлений на странице заявлений поступающего: `Вы уверены, что хотите удалить заявление? Вы не сможете отменить данное действие. В вашем заявлении есть направления подготовки, прием заявлений по которым прекращен.`'
                                            )];
                                        } ?>
                                        <?php $deleteBtnLabel = Yii::t(
                                            'abiturient/applications/application-table',
                                            'Подпись кнопки удаления заявления; в таблице заявлений на странице заявлений поступающего: `Удалить`'
                                        ); ?>
                                        <?= Html::submitButton(
                                            "<i class='fa fa-remove' aria-hidden='true'></i> {$deleteBtnLabel}",
                                            [
                                                'class' => 'btn btn-outline-secondary btn-block',
                                                'data' => $data
                                            ]
                                        ); ?>
                                        <?= Html::endForm(); ?>
                                    <?php endif; ?>
                                <?php elseif ($application->draft_status == IDraftable::DRAFT_STATUS_APPROVED) : ?>
                                    <?php $url = Url::toRoute([
                                        'bachelor/application',
                                        'id' => $application->id
                                    ]) ?>
                                    <a href="<?= $url; ?>" class="btn btn-outline-secondary btn-applications">
                                        <i class="fa fa-eye" aria-hidden="true"></i>

                                        <?= Yii::t(
                                            'abiturient/applications/application-table',
                                            'Подпись ссылки переадресующей к заявлению, которое одобрено; в таблице заявлений на странице заявлений поступающего: `Просмотреть`'
                                        ); ?>
                                    </a>
                                    <?php $url = Url::toRoute([
                                        $sandBoxEnabled->value == 1 ? '/abiturient/mark-return-application' : '/abiturient/return-application',
                                        'id' => $application->id
                                    ]); ?>
                                    <?php $deleteBtnLabel = Yii::t(
                                        'abiturient/applications/application-table',
                                        'Подпись кнопки отзыва заявления; в таблице заявлений на странице заявлений поступающего: `Отозвать`'
                                    ); ?>
                                    <?= Html::a(
                                        "<i class='fa fa-remove' aria-hidden='true'></i> {$deleteBtnLabel}",
                                        '#',
                                        [
                                            'class' => 'btn btn-outline-secondary btn-block',
                                            'data-toggle' => 'modal',
                                            'data-target' => "#application-return-modal-{$application->id}"
                                        ]
                                    ); ?>
                                    <?php echo $this->render('application_partial/_application_return_modal', [
                                        'application' => $application,
                                        'attachments' => [$application->getApplicationReturnAttachmentCollection()],
                                        'url' => $url
                                    ]); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($application->specialities) : ?>
                            <?php $i = 1; ?>
                            <?php foreach ($application->specialities as $spec) : ?>
                                <?php  ?>

                                <tr class="no-top <?php if ($i == sizeof($application->specialities)) : ?>with-bottom<?php endif; ?>">
                                    <td class="spec-td">
                                        <?php $specs = $application->specialities; ?>
                                        <?php $accum = []; ?>

                                        <?php if ($application->type->display_code) {
                                            if (!empty($spec->speciality->speciality_human_code)) {
                                                $accum[] = $spec->speciality->speciality_human_code;
                                            }
                                        }

                                        if ($application->type->display_speciality_name) {
                                            if (!empty($spec->speciality->directionRef)) {
                                                $accum[] = $spec->speciality->directionRef->reference_name;
                                            }
                                        }

                                        if (!empty($spec->speciality->profileRef)) {
                                            $accum[] = $spec->speciality->profileRef->reference_name;
                                        }

                                        if ($application->type->display_group_name) {
                                            if (!empty($spec->speciality->competitiveGroupRef)) {
                                                $accum[] = $spec->speciality->competitiveGroupRef->reference_name;
                                            }
                                        }

                                        if (count($accum)) {
                                            echo implode(' ', $accum);
                                        } ?>
                                    </td>

                                    <td>
                                        <?= $spec->speciality->educationLevelRef->reference_name ?? ''; ?>
                                    </td>

                                    <td>
                                        <?= $spec->speciality->educationFormRef->reference_name ?? ''; ?>
                                        <?= Yii::t(
                                            'abiturient/applications/application-table',
                                            'Постфикс для поля "Форма обучения"; в таблице заявлений на странице заявлений поступающего: `форма обучения`'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php if ($application->status == BachelorApplication::STATUS_APPROVED && $application->type->show_list) : ?>
                                            <?php $position = $spec->countPosition(); ?>
                                            <?php if ($position) : ?>
                                                <a target="_blank" href="<?= Url::toRoute(['admission/showlist', 'id' => $spec->id]); ?>">
                                                    <?= $position; ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <td></td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <td colspan="4">
                        <?= Yii::t(
                            'abiturient/applications/application-table',
                            'Текст сообщения об отсутствии заявлений; в таблице заявлений на странице заявлений поступающего: `Нет поданных заявлений`'
                        ); ?>
                    </td>
                <?php endif; ?>
            </table>
        </div>

        <?php $applicationsBottomText = Yii::$app->configurationManager->getText('applications_bottom_text', $type_for_texts); ?>
        <?php if ($applicationsBottomText) : ?>
            <div class="clear-both"></div>

            <div class="alert alert-info" role="alert">
                <?= $applicationsBottomText; ?>
            </div>
        <?php endif; ?>
    </div>