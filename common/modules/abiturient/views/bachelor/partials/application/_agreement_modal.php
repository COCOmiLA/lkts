<?php

use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use kartik\file\FileInput;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;





$appLanguage = Yii::$app->language;

?>

<div class="modal fade" id="agreementModal" tabindex="-1" role="dialog" aria-labelledby="agreementModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-modal',
                        'Заголовок модального окна согласия на странице НП: `Согласие на зачисление`'
                    ) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(Url::toRoute('bachelor/add-agree'), 'POST', ['enctype' => 'multipart/form-data']); ?>

            <div class="modal-body ia-modal">
                <div class="row">
                    <div class="col-12">
                        <p>
                            <?= Yii::$app->configurationManager->getText('info_agreement', $application->type ?? null); ?>
                        </p>

                        <?php if ($applicationAgreementInfo = Yii::$app->configurationManager->getText('application_agreement_info', $application->type ?? null)) : ?>
                            <div class="alert alert-warning" role="alert">
                                <?= $applicationAgreementInfo; ?>
                            </div>
                        <?php endif; ?>
                        <?php
                        if ($application->isPrintApplicationByFullPackageAvailable()) {
                            $message = Yii::t(
                                'abiturient/bachelor/application/agreement-modal',
                                'Надпись на ссылки для скачивания сформированного бланка; модального окна согласия на странице НП: `Бланк согласия на зачисление`'
                            );
                            $download_url = [
                                'bachelor/print-application-by-full-package',
                                'application_id' => $application->id,
                                'report_type' => 'Agreement'
                            ];

                            echo Html::a(
                                $message,
                                $download_url,
                                ['target' => '_blank']
                            );
                        } else {
                            $need_fill_data = Yii::t(
                                'abiturient/bachelor/application/agreement-modal',
                                'Текст сообщения; модального окна согласия на странице НП: `Прикрепление согласия на зачисление возможно после приёма заполнения данных об образовании и выбора направлений подготовки`'
                            );
                            ?>
                            <div class="alert alert-warning" role="alert">
                                <p><?php echo $need_fill_data; ?></p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="row required" id="add_file_input">
                    <div class="col-12">
                        <label class="col-form-label has-star">
                            <?= Yii::t(
                                'abiturient/bachelor/application/agreement-modal',
                                'Подпись для поля прикрепления бланка; модального окна согласия на странице НП: `Скан-копия согласия:`'
                            ) ?>
                        </label>
                    </div>

                    <div class="col-12">
                        <input type="hidden" id="agreespec_id" name="spec_id" value=""/>

                        <?= FileInput::widget([
                            'name' => 'AdmissionAgreement[file]',
                            'language' => $appLanguage,
                            'id' => 'file',
                            'options' => ['multiple' => false, 'id' => 'file'],
                            'pluginOptions' => [
                                'required' => true,
                                'showRemove' => true,
                                'showCaption' => true,
                                'showUpload' => false,
                                'showPreview' => false,
                                'allowedFileExtensions' => AdmissionAgreement::getExtensionsList(),
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/agreement-modal',
                        'Подпись кнопки отмены; модального окна согласия на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $btnName = Yii::t(
                    'abiturient/bachelor/application/agreement-modal',
                    'Подпись кнопки сохранения; модального окна согласия на странице НП: `Сохранить`'
                ) ?>
                <input type="submit" class="btn btn-primary" id="add-ia" value="<?= $btnName ?>"/>
            </div>

            <?= Html::endForm(); ?>
        </div>
    </div>
</div>