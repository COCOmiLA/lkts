<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;

?>

<div class="statuses">
    <?php if ($hasError) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Yii::t('abiturient/bachelor/application', 'Текст сообщения в случае неудачной подачи заявления в приёмную кампанию: `Не удалось отправить заявление в приёмную кампанию.`') ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (
        $agreeError &&
        $needAgreement = Yii::$app->configurationManager->getText('need_agreement', $application->type ?? null)
    ) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $needAgreement ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (
        $application->getStatusMessage() &&
        $application->status != BachelorApplication::STATUS_APPROVED &&
        $application->status != BachelorApplication::STATUS_NOT_APPROVED &&
        $application->status != BachelorApplication::STATUS_REJECTED_BY1C
    ) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $application->getStatusMessage() ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif ($application->getStatusMessage() && $application->status == BachelorApplication::STATUS_APPROVED) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $application->getStatusMessage() ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <?php if ($application->moderator_comment) : ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= Yii::$app->configurationManager->getText('moder_comment', $application->type ?? null) . ' ' . $application->formattedModeratorComment ?>

                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    <?php elseif ($application->getStatusMessage() && $application->status == BachelorApplication::STATUS_NOT_APPROVED) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $application->getStatusMessage() ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <?php if ($application->moderator_comment) : ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= Yii::$app->configurationManager->getText('moder_comment', $application->type ?? null) . ' ' . $application->formattedModeratorComment ?>

                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    <?php elseif ($application->getStatusMessage() && $application->status == BachelorApplication::STATUS_REJECTED_BY1C) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $application->getStatusMessage() ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
</div>