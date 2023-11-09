<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\helpers\Html;







?>

<?php if (
    $hasError &&
    $saveError = Yii::$app->configurationManager->getText('save_error', $application->type ?? null)
) : ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $saveError; ?>
    </div>
<?php endif; ?>

<?php if (
    $application->getEgeStatusMessage() &&
    $application->status != BachelorApplication::STATUS_APPROVED &&
    $application->status != BachelorApplication::STATUS_NOT_APPROVED &&
    $application->status != BachelorApplication::STATUS_REJECTED_BY1C
) : ?>
    <div class="alert alert-info" role="alert">
        <?php echo $application->getEgeStatusMessage(); ?>
    </div>
<?php elseif (
    $application->getEgeStatusMessage() &&
    $application->status == BachelorApplication::STATUS_APPROVED
) : ?>
    <div class="alert alert-success" role="alert">
        <?php echo $application->getEgeStatusMessage(); ?>
        <br>
    </div>

    <?php if ($application->moderator_comment) : ?>
        <div class="alert alert-info">
            <?php echo Yii::$app->configurationManager->getText('moder_comment', $application->type ?? null); ?>

            <?= $application->formattedModeratorComment; ?>
        </div>
    <?php endif; ?>
<?php elseif (
    $application->getEgeStatusMessage() &&
    $application->status == BachelorApplication::STATUS_NOT_APPROVED
) : ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $application->getEgeStatusMessage(); ?>
    </div>

    <?php if ($application->moderator_comment) : ?>
        <div class="alert alert-info">
            <?php echo Yii::$app->configurationManager->getText('moder_comment', $application->type ?? null); ?>

            <?= $application->formattedModeratorComment; ?>
        </div>
    <?php endif; ?>
<?php elseif (
    $application->getEgeStatusMessage() &&
    $application->status == BachelorApplication::STATUS_REJECTED_BY1C
) : ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $application->getEgeStatusMessage(); ?>
    </div>
<?php endif;