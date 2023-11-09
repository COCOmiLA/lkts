<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;





?>

<div class="modal fade" id="agreementDeclineNonVerifiedModal" tabindex="-1" role="dialog" aria-labelledby="agreementDeclineNonVerifiedModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myDeclineModalLabel">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Заголовок модального окна отзыва не проверенного согласия на странице НП: `Отзыв согласия на зачисление`'
                    ) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(Url::toRoute('bachelor/decline-agreement'), 'POST', ['enctype' => 'multipart/form-data']); ?>

            <div class="modal-body ia-modal">
                <p>
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Текст сообщения; модального окна отзыва не проверенного согласия на странице НП: `Вы действительно хотите отозвать согласие на зачисление?`'
                    ) ?>
                </p>

                <input type="hidden" id="non_verified_agreement_id_to_decline" name="agreement_id" value="" />
            </div>

            <div class="modal-footer">
                <button type="button" id="close_non_verified" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Подпись кнопки отмены; модального окна отзыва не проверенного согласия на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $btnName = Yii::t(
                    'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                    'Подпись кнопки отзыва; модального окна отзыва не проверенного согласия на странице НП: `Отозвать`'
                ) ?>
                <input type="submit" class="btn btn-primary" id="decline_non_verified_agreement" value="<?= $btnName ?>" />
            </div>

            <?= Html::endForm(); ?>
        </div>
    </div>
</div>