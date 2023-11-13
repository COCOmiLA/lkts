<?php

use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\helpers\Html;

if (
    Yii::$app->session->getFlash('questionaryIsSaved', false) &&
    $questionarySaveSuccess = Yii::$app->configurationManager->getText('questionary_save_success')
) {
    echo Html::tag(
        'div',
        $questionarySaveSuccess,
        ['class' => 'alert alert-success', 'role' => 'alert']
    );
}

if (
    $hasErrors &&
    $saveError = Yii::$app->configurationManager->getText('save_error')
) {
    echo Html::tag(
        'div',
        $saveError,
        ['class' => 'alert alert-danger', 'role' => 'alert']
    );
}

$questionary_message = $questionary->getStatusMessage();
if (!empty($questionary_message)) {
    $status = intval($questionary->status);
    if (in_array($status, [AbiturientQuestionary::STATUS_CREATED, AbiturientQuestionary::STATUS_SENT])) {
        $questionary_class = 'alert alert-info';
    } elseif ($status === AbiturientQuestionary::STATUS_APPROVED) {
        $questionary_class = 'alert alert-success';
    } elseif ($status === AbiturientQuestionary::STATUS_CREATE_FROM_1C) {
        $questionary_class = 'alert alert-success';
    } elseif (in_array($status, [AbiturientQuestionary::STATUS_NOT_APPROVED, AbiturientQuestionary::STATUS_REJECTED_BY1C])) {
        $questionary_class = 'alert alert-danger';
    }

    echo Html::tag('div', $questionary_message, ['class' => $questionary_class, 'role' => 'alert']);
}

if ($questionary->draft_status != IDraftable::DRAFT_STATUS_APPROVED && !$questionary->linkedBachelorApplication && !$questionary->isPassportsRequiredFilesAttached()) {
    echo Html::tag(
        'div',
        Yii::t(
            'abiturient/questionary/all',
            'Текст ошибки при отсутствии скан-копии паспорта в анкете: `Отсутствует скан-копия паспорта, необходимо отредактировать Паспортные данные.`'
        ) 
        . "</br>" 
        . Html::a(
            Yii::t('abiturient/questionary/all', 'Текст ссылки для открытия модального окна паспорта с отсутствующей скан-копией: `Загрузить скан-копию`'), '#', [
                'data-toggle' => 'modal',
                'class' => 'btn-edit-passport',
                'data-id' => $questionary->onePassportWithoutFile->id ?? null,
                'data-document' => $questionary->onePassportWithoutFile->document_type_id ?? null,
            ]
        ),
        ['id' => 'alert-missing-passport', 'class' => 'alert alert-danger', 'role' => 'alert']
    );
} ?>

<?php if (
    $currentApplication &&
    $currentApplication->getStatusMessage() &&
    in_array($currentApplication->status, [BachelorApplication::STATUS_APPROVED, $currentApplication->status == BachelorApplication::STATUS_NOT_APPROVED])
) : ?>
    <?php $alertType = $currentApplication->status == BachelorApplication::STATUS_APPROVED ? 'success' : 'danger'; ?>
    <div class="alert alert-<?= $alertType ?> alert-dismissible" role="alert">
        <?= $currentApplication->getStatusMessage(); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <?php if ($currentApplication->moderator_comment) : ?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <?= Yii::$app->configurationManager->getText('moder_comment', $currentApplication->type ?? null); ?>

            <?= $currentApplication->formattedModeratorComment; ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
<?php endif;
