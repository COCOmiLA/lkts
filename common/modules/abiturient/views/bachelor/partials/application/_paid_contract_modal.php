<?php

use common\models\Attachment;
use kartik\file\FileInput;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;




$appLanguage = Yii::$app->language;

?>

<div class="modal fade" id="paidContract" tabindex="-1" role="dialog" aria-labelledby="paidContractLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <?= Yii::t(
                        'abiturient/bachelor/application/paid-contract-modal',
                        'Заголовок модального окна договора на странице НП: `Договор об оказании платных образовательных услуг`'
                    ) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(Url::toRoute('/bachelor/add-paid-contract'), 'POST', ['enctype' => 'multipart/form-data', 'id' => 'add-paid-contract-form']); ?>

            <div class="modal-body add-paid-modal">
                <div class="row">
                    <div class="col-12">
                        <p>
                            <?= Yii::t(
                                'abiturient/bachelor/application/paid-contract-modal',
                                'Текст сообщения; модального окна договора на странице НП: `Для прикрепления договора необходимо скачать бланк документа, распечатать его, заполнить, подписать, отсканировать и прикрепить обратно.`'
                            ) ?>
                        </p>

                        <?= Html::a(
                            Yii::t(
                                'abiturient/bachelor/application/paid-contract-modal',
                                'Надпись на ссылки для скачивания пустого бланка; модального окна договора на странице НП: `Пустой договор`'
                            ),
                            ['/site/download-paid-contract'],
                            ['target' => '_blank', 'id' => 'download-paid-contract-template']
                        ); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label class="col-form-label">
                            <?= Yii::t(
                                'abiturient/bachelor/application/paid-contract-modal',
                                'Подпись для поля прикрепления договора; модального окна договора на странице НП: `Скан-копия договора:`'
                            ) ?>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?= FileInput::widget([
                            'name' => 'Attachment[file]',
                            'language' => $appLanguage,
                            'id' => 'paid-contract-file',
                            'options' => ['multiple' => false, 'id' => 'paid-contract-file'],
                            'pluginOptions' => [
                                'required' => true,
                                'showRemove' => true,
                                'showCaption' => true,
                                'showUpload' => false,
                                'showPreview' => false,
                                'allowedFileExtensions' => Attachment::getExtensionsList(),
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/paid-contract-modal',
                        'Подпись кнопки отмены; модального окна договора на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $btnName = Yii::t(
                    'abiturient/bachelor/application/paid-contract-modal',
                    'Подпись кнопки сохранения; модального окна договора на странице НП: `Сохранить`'
                ) ?>
                <input type="submit" class="btn btn-primary" id="add-paid-contract-button" value="<?= $btnName ?>">
            </div>

            <?= Html::endForm(); ?>
        </div>
    </div>
</div>