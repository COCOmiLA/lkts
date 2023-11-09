<?php

use common\components\applyingSteps\ApplicationApplyingStep;
use yii\web\View;







?>

<div class="card mb-3 text-white bg-info application-send-status">
    <div class="card-header">
        <h4>
            <?= Yii::t('sandbox/moderate/errors', 'Заголовок блока с ошибками "статуса отправки": `Статус отправки заявления.`') ?>
        </h4>
    </div>

    <div class="card-body p-0">
        <?php foreach ($stepsInfo as $step) : ?>
            <?php $color = "";
            switch ($step['status']) {
                case ApplicationApplyingStep::STEP_STATUS_FAILED:
                    $color = 'alert-danger';
                    break;
                case ApplicationApplyingStep::STEP_STATUS_UNTOUCHED:
                    $color = 'alert-warning';
                    break;
                case ApplicationApplyingStep::STEP_STATUS_VALID:
                    $color = 'alert-success';
                    break;
            } ?>

            <div class="alert <?= $color ?> mb-0">
                <strong><?= $step['name'] ?></strong> <?= $step['statusMessage'] ?>

                <?php if ($step['status'] == ApplicationApplyingStep::STEP_STATUS_FAILED) : ?>
                    <a href="#errors-<?= $step['shortName'] ?>" data-toggle="collapse">
                        <?= Yii::t('sandbox/moderate/errors', 'Подпись кнопки раскрытия свёрнутого списка в блока с ошибками "статуса отправки": `Посмотреть ошибки.`') ?>
                    </a>
                <?php endif; ?>

                <?php if ($step['status'] == ApplicationApplyingStep::STEP_STATUS_FAILED) : ?>
                    <div id="errors-<?= $step['shortName'] ?>" class="errors-down collapse">
                        <ul class="mb-0 pl-4">
                            <?php foreach ($step['errors'] as $error) : ?>
                                <li><?= nl2br($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>