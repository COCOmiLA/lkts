<?php

use common\modules\abiturient\models\bachelor\AgreementDecline;
use kartik\file\FileInput;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;




$appLanguage = Yii::$app->language;

?>

<div class="modal fade" id="agreementDeclineModal" tabindex="-1" role="dialog" aria-labelledby="agreementDeclineModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myDeclineModalLabel">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-modal',
                        'Заголовок модального окна отзыва согласия на странице НП: `Отзыв согласия на зачисление`'
                    ) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(Url::toRoute('bachelor/decline-agreement'), 'POST', ['enctype' => 'multipart/form-data']); ?>

            <div class="modal-body ia-modal">
                <div class="row">
                    <div class="col-12">
                        <p>
                            <?= Yii::$app->configurationManager->getText('info_agreement_decline'); ?>
                        </p>

                        <?= Html::a(
                            Yii::t(
                                'abiturient/bachelor/application/agreement-decline-modal',
                                'Текст ссылки на скачивание пустого бланка; модального окна отзыва согласия на странице НП: `Пустой бланк отзыва согласия на зачисление`'
                            ),
                            ['/bachelor/print-application-return-form', 'application_id' => $application->id, 'type' => 'AgreementReturn'],
                            ['target' => '_blank']
                        ); ?>
                    </div>
                </div>

                <div class="row required" id="add_file_input">
                    <div class="col-12">
                        <label class="col-form-label has-star">
                            <?= Yii::t(
                                'abiturient/bachelor/application/agreement-decline-modal',
                                'Подпись поля для прикрепления бланка; модального окна отзыва согласия на странице НП: `Скан-копия отзыва согласия согласия:`'
                            ) ?>
                        </label>
                    </div>

                    <div class="col-12">
                        <input type="hidden" id="agreement_id_to_decline" name="agreement_id" value="" />

                        <?= FileInput::widget([
                            'name' => 'AgreementDecline[file]',
                            'language' => $appLanguage,
                            'id' => 'file',
                            'options' => ['multiple' => false, 'id' => 'file-to-decline'],
                            'pluginOptions' => [
                                'required' => true,
                                'showRemove' => true,
                                'showCaption' => true,
                                'showUpload' => false,
                                'showPreview' => false,
                                'allowedFileExtensions' => AgreementDecline::getExtensionsList(),
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="close_verified" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-decline-modal',
                        'Подпись кнопки отмены; модального окна отзыва согласия на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $btnName = Yii::t(
                    'abiturient/bachelor/application/agreement-decline-modal',
                    'Подпись кнопки отзыва; модального окна отзыва согласия на странице НП: `Отозвать`'
                ) ?>
                <input type="submit" class="btn btn-primary" id="decline_agreement" value="<?= $btnName ?>" />
            </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
</div>