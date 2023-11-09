<?php

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use yii\web\View;







?>

<div class="row change--row">
    <div class="change--col change--col-time col-3 text-right">
        <span class="change--time">
            <?= date('H:i:s d.m.Y', $historyRow->created_at) ?>
        </span>
    </div>

    <div class="change--col change--col-icon col-1 text-left no-padding ">
        <span class="d-flex justify-content-center align-items-center badge change-icon--badge icon--<?= $historyRow->getRow()->getIconColor() ?>">
            <span class="<?= $historyRow->getRow()->getIcon() ?> change-icon " aria-hidden="true"></span>
        </span>

        <span class="vertical-line">
    </div>

    <div class="col-8">
        <div class="change--content-wrapper overflow-word-wrapper">
            <div class="change--col-content change--title change--col-time">
                <?= $historyRow->getRow()->getRowTitle() ?>

                <br>

                <span class="badge badge-secondary">
                    <?= Yii::t(
                        'abiturient/change-history-widget',
                        'Текст указателя инициатора виджета истории изменений: `Инициатор: <strong>{initiator}.</strong>`',
                        ['initiator' => $historyRow->getRow()->getInitiator()]
                    ) ?>
                </span>
            </div>

            <?php $body = $historyRow->getRow()->getContent(); ?>
            <?php if (!empty($body)) : ?>
                <div class="change--col-content" style="margin-top: 10px">
                    <a class="btn btn-primary" data-toggle="collapse" href="#collapse-change-<?= $historyRow->id ?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                        <?= Yii::t(
                            'abiturient/change-history-widget',
                            'Подпись кнопки раскрытия подробного списка с изменениями виджета истории изменений: `Подробнее`'
                        ) ?>
                    </a>

                    <div class="collapse change-collapse" id="collapse-change-<?= $historyRow->id ?>">
                        <div class="card mb-3 card-body" style="padding: 15px">
                            <?php foreach ($body as $data) {
                                echo $this->render($data['view'], $data['data']);
                            } ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>