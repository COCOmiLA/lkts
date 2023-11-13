<?php

use common\models\attachment\attachmentCollection\BaseAttachmentCollection;
use common\models\interfaces\FileToShowInterface;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\services\NextStepService;
use kartik\form\ActiveForm;
use yii\bootstrap4\Alert;
use yii\helpers\Html;
use yii\web\View;








$hasPassedApplication = false;
if ($application) {
    $hasPassedApplication = $application->hasPassedApplication();
}

$this->title = Yii::$app->name . ' | ' . Yii::t(
        'abiturient/bachelor/load-scans/all',
        'Заголовок страницы сканов: `Личный кабинет поступающего | Сканы документов`'
    );

$disabled = "";
$isReadonly = false;
$formId = 'load-scan-form';
if (!$application->canEdit() || !$application->canEditSpecialities()) {
    $disabled = 'disabled';
    $isReadonly = true;
}
echo $this->render(
    '../abiturient/_abiturientheader',
    [
        'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
        'current_application' => $application
    ]
);

if (!$isAttachmentsAdded && $application->canEdit() && $application->canEditSpecialities()) {
    echo $this->render(
        '../abiturient/_fileError',
        ['attachmentErrors' => $attachmentErrors]
    );
}

?>

<?php if ($loadScans = Yii::$app->configurationManager->getText('load_scans', $application->type ?? null)) : ?>
    <div class="alert alert-info" role="alert">
        <?= $loadScans; ?>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin([
    'id' => $formId,
    'options' => ['enctype' => 'multipart/form-data'],
]);

$submit_button_html_to_inject = '';
ob_start();
?>

<div class="col-12">
    <?php
    $message = Yii::t(
        'abiturient/bachelor/load-scans/all',
        'Подпись кнопки сохранения формы прикрепления сканов; на странице сканов: `Сохранить`'
    );
    $next_step_service = new NextStepService($application);
    if ($next_step_service->getUseNextStepForwarding()) {
        $message = Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Подпись кнопки сохранения формы прикрепления сканов; на странице сканов: `Сохранить и перейти к следующему шагу`'
        );
    }
    echo Html::submitButton(
        $message,
        ['class' => 'btn btn-primary float-right', 'style' => 'margin-right: 25px;']
    );
    ?>
</div>

<?php
$submit_button_html_to_inject = ob_get_clean();
?>
<div class="row">
    <?php foreach ($full_attachments_package as $name => $attachments) : ?>
        <?php if (!empty($attachments)) : ?>
            <?php
            
            usort($attachments, function (BaseAttachmentCollection $a, BaseAttachmentCollection $b) {
                if ($a->attachments && $b->attachments) {
                    return 0;
                }
                return (!$b->attachments) ? -1 : 1;
            })
            ?>
            <?= $this->render(
                '@abiturient/views/partial/fileComponent/_attachments',
                [
                    'formId' => $formId,
                    'minify' => $application->type->minify_scans_page,
                    'app' => $application,
                    'isReadonly' => $isReadonly,
                    'attachments' => $attachments,
                    'formName' => $name ?? Yii::t(
                            'abiturient/bachelor/load-scans/all',
                            'Заголовок блока сканов по умолчанию: `Скан-копии документов`'
                        ),
                    'submit_block' => $submit_button_html_to_inject
                ]
            ); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (!empty($regulations)) : ?>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'abiturient/bachelor/load-scans/all',
                            'Заголовок блока нормативных документов по умолчанию: `Нормативные документы`'
                        ) ?>
                    </h4>
                </div>

                <div class="card-body">
                    <?php foreach ($regulations as $regulation) : ?>
                        <?php $attachment = $regulation->getAttachmentCollection() ?>
                        <?php if (!$attachment instanceof FileToShowInterface) : ?>
                            <?= Alert::widget([
                                'body' => Yii::t(
                                    'abiturient/attachment-widget',
                                    'Тест ошибки использования неправильного типа скан-копии виджета сканов: `Ожидался класс исполняющий интерфейс FileToShowInterface`'
                                ),
                                'options' => ['class' => 'alert-danger']
                            ]); ?>
                        <?php else : ?>
                            <div class="row">
                                <div class="col-12">
                                    <strong><?= $regulation->regulation->name ?></strong>
                                </div>
                            </div>

                            <?php if (!$attachment->isHidden()) {
                                echo $this->render('@abiturient/views/partial/fileComponent/_attachment', [
                                    'attachment' => $attachment,
                                    'isReadonly' => $isReadonly,
                                    'hasPassedApplication' => $hasPassedApplication,
                                    'performRegulation' => true,
                                    'minify' => $application->type->minify_scans_page,
                                ]);
                            } ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <?= $this->render(
                '@common/view/_file_size_validator',
                ['formId' => $formId]
            ); ?>
        </div>

        <div class="col-12">
            <div class="row">
                <?= $submit_button_html_to_inject ?>
            </div>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end() ?>
</div>