<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;





?>

<div class="modal fade" id="agreementDeclineRemoveModal" tabindex="-1" role="dialog" aria-labelledby="agreementDeclineRemoveModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myDeclineRemoveModalLabel">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Заголовок модального окна отмены отказа от согласия на странице НП: `Отмена отказа от согласия на зачисление`'
                    ) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(Url::toRoute('bachelor/remove-agreement-decline'), 'POST', ['enctype' => 'multipart/form-data']); ?>

            <div class="modal-body ia-modal">
                <p>
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Текст сообщения; модального окна отмены не проверенного отказа от согласия на странице НП: `Вы действительно хотите отменить отзыв согласия на зачисление?`'
                    ) ?>
                </p>

                <input type="hidden" id="agreement_decline_id_to_remove" name="agreement_decline_id" value="" />
            </div>

            <div class="modal-footer">
                <button type="button" id="close_non_verified" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                        'Подпись кнопки отмены; модального окна отмены отказа от согласия на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $btnName = Yii::t(
                    'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                    'Подпись кнопки отмены; модального окна отмены отказа от согласия на странице НП: `Отменить отказ`'
                ) ?>
                <input type="submit" class="btn btn-primary" id="remove_agreement_decline" value="<?= $btnName ?>" />
            </div>

            <?= Html::endForm(); ?>
        </div>
    </div>
</div>