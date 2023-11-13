<?php

use common\components\ini\iniGet;
use common\models\Attachment;
use common\models\interfaces\FileToShowInterface;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;











$appLanguage = Yii::$app->language;
$i = $attachmentCollection->getIndex();
$minify = $minify ?? false;
$addNewFile = $addNewFile ?? true;
$canDeleteFile = $canDeleteFile ?? true;
$container_id = ($container_id ?? null);
$message = Yii::t(
    'abiturient/attachment-widget',
    'Обычный текст сообщения подтверждающий удаление файла виджета сканов: `Вы уверены что хотите удалить прикреплённый файл?`'
);
$attachment_is_required = $attachmentCollection->isRequired();
$required = $required ?? $attachment_is_required;

$multiple = $multiple ?? true;


if (!$attachmentCollection->attachments && $attachment_is_required) {
    $addNewFile = true;
}
$alertText = Yii::t(
    'abiturient/attachment-widget',
    'Текст сообщения алерта при попытки удаление единственного обязательного для прикрепления файла виджета сканов: `Вы пытаетесь удалить последнюю скан-копию. Для данного типа документа наличие скан-копий обязательно. Пожалуйста, добавьте другой файл и нажмите кнопку "Сохранить". После этого удаление текущего файла будет возможно.`'
);

$attachmentFile = new Attachment();

$attr = "file[{$i}][]";

$model = ($model ?? $attachmentCollection->getModelEntity());
$id = $id ?? "{$model->formName()}{$i}";

$change_callback = <<<JS
    function (event) {
        var target = event.target;
        var input = $(target);
        if (+input.data("need_one_doc") === 1 && +input.data("is_required") === 1) {
            var document_set_uid = input.data("document_set_uid");
            if (document_set_uid) {
                $('input[type="file"]')
                    .filter(".attachment_file")
                    .filter('[data-document_set_uid="' + document_set_uid + '"]')
                    .not(target)
                    .each(function () {
                        var self = $(this);
                        self.data("fileinput").required = false;
                        if (self.data("container_id")) {
                            var container = $("#" + self.data("container_id"));
                            container.removeClass("required");
                        }
                    });
                // автоматом не скрываются ошибки
                $(".file-input.has-error").removeClass("has-error");
                $(".file-error-message").hide();
            }
        }
    }
JS;

$files_check_callback = <<<JS
    function (event) {
        var target = event.target;
        var input = $(target);
        if (+input.data("need_one_doc") === 1 && +input.data("is_required") === 1) {
            var document_set_uid = input.data("document_set_uid");
            if (document_set_uid) {
                var document_set_selector = $('input[type="file"]')
                    .filter(".attachment_file")
                    .filter('[data-document_set_uid="' + document_set_uid + '"]');

                var filled_inputs_in_document_set = document_set_selector.filter(function () {
                    // в форме прикреплён файл
                    if (this.files && this.files.length > 0) {
                        return true;
                    }
                    // файл уже сохранён ранее
                    var preview = $(this).fileinput("getPreview");
                    return !!(preview && preview.content.length > 0);
                });

                // проверяем есть ли для текущего набора документов прикреплённые файлы к любому attachment_type
                // все инпуты набора документов требуют файл если ни в одном из них нет уже загруженных
                var needs_required = filled_inputs_in_document_set.length === 0;
                document_set_selector.each(function () {
                    var self = $(this);
                    self.data("fileinput").required = needs_required;
                    if (self.data("container_id")) {
                        var container = $("#" + self.data("container_id"));
                        if (needs_required) {
                            container.addClass("required");
                        } else {
                            container.removeClass("required");
                        }
                    }
                });
                // автоматом не скрываются ошибки
                $(".file-input.has-error").removeClass("has-error");
                $(".file-error-message").hide();
            }
        }
    }
JS;

$filepredelete_callback = <<<JS
    function (event) {
        var target = $(event.target);
        target
            .parents('form')
            .on('beforeValidate', function(e) {
                target.data('fileinput').ajaxAborted = false;
            });
        if (+target.data("is_required") === 1) {
            var preview = target.fileinput("getPreview");
            var is_last_file = preview.content.length === 1;
            if (is_last_file && +target.data("need_one_doc") === 1) {
                var document_set_uid = target.data("document_set_uid");
                if (document_set_uid) {
                    var filled_inputs_in_document_set = $('input[type="file"]')
                        .filter(".attachment_file")
                        .filter('[data-document_set_uid="' + document_set_uid + '"]')
                        .filter(function () {
                            // файл уже сохранён ранее
                            var preview = $(this).fileinput("getPreview");
                            return !!(preview && preview.content.length > 0);
                        });
                    is_last_file = filled_inputs_in_document_set.length === 0;
                }
            }
            if (is_last_file) {
                alert('{$alertText}');
                return true;
            }
        }
        return !confirm("{$message}");
    }
JS;
$initialPreviews = $attachmentCollection->getInitialPreviews();
$initialPreviewConfig = $attachmentCollection->getInitialPreviewConfig();

$config = [
    'id' => $id,
    'model' => $model,
    'attribute' => $attr,
    'language' => $appLanguage,
    'disabled' => $isReadonly,
    'options' => [
        'id' => $id,
        'class' => 'attachment_file',
        'multiple' => $multiple,
        'data' => [
            'is_required' => (int)$attachment_is_required,
            'need_one_doc' => (int)!!ArrayHelper::getValue($attachmentCollection, 'attachmentType.need_one_of_documents'),
            'document_set_uid' => ArrayHelper::getValue($attachmentCollection, 'attachmentType.documentSetRef.reference_uid'),
            'container_id' => $container_id,
        ],
    ],
    'pluginOptions' => [
        'theme' => 'fa4',
        'msgProcessing' => Yii::t(
            'abiturient/attachment-widget',
            'Текст сообщения обработки скана виджета сканов: `Обработка ...`'
        ),
        'allowedFileExtensions' => Attachment::getExtensionsList(),
        'required' => $required && !$initialPreviews,
        'removeClass' => 'btn btn-danger',

        'overwriteInitial' => false,
        'initialPreviewDownloadUrl' => $attachmentCollection->getFileDownloadUrl(),
        'initialPreview' => $initialPreviews,
        'deleteUrl' => $canDeleteFile ? $attachmentCollection->getFileDeleteUrl() : '',

        'initialPreviewAsData' => true,
        'initialPreviewConfig' => $initialPreviewConfig,
        'removeLabel' => Yii::t(
            'abiturient/attachment-widget',
            'Подпись кнопки очистки добавленных файлов виджета сканов: `Очистить`'
        ),
        'removeFromPreviewOnError' => true,
        'showPreview' => !$minify,
        'showCaption' => true,
        'showUpload' => false,
        'showBrowse' => $addNewFile,
        'initialPreviewShowDelete' => $canDeleteFile, 
        'showRemove' => !$minify && $canDeleteFile,
        'showClose' => false,
        'showDownload' => true,
        'showDelete' => false,
        'maxFileSize' => iniGet::getUploadMaxFilesize(),
        'dropZoneEnabled' => $addNewFile,
    ],
    'pluginEvents' => array_merge(
        [
            'filepredelete' => $filepredelete_callback,
            'change' => $change_callback,
            'filecleared' => $files_check_callback,
            'filedeleted' => $files_check_callback,
        ],
        $pluginEvents ?? []
    )
];

$file_input = null;
if (isset($form)) {
    $attachmentTypeTemplate = ArrayHelper::getValue($attachmentCollection, 'attachmentType.attachmentTypeTemplate');
    if ($attachmentTypeTemplate) {
        $label = $this->render(
            '@abiturient/views/partial/fileComponent/_modal_attachment_template_preview',
            [
                'attachmentTypeLabel' => $label,
                'attachmentTypeTemplate' => $attachmentTypeTemplate,
            ]
        );
    }

    


    $file_input = $form->field($model, $attr ?? "file[{$i}][]")
        ->widget(FileInput::class, $config)->label($label ?? false);
} else {
    $file_input = FileInput::widget($config);
}

?>

<?php if ($minify) : ?>
    <div class="row">
        <div class="col-12">
            <?php foreach ($attachmentCollection->attachments as $attachment) : ?>
                <p>
                    <?php echo Html::a($attachment->filename, $attachment->getFileDownloadUrl()) ?>
                    <?php if (!(count($attachmentCollection->attachments) == 1 && $attachmentCollection->isRequired())) : ?>
                        &#9;
                        <?php echo Html::a("<i class='fa fa-remove'></i>", $attachment->getFileDeleteUrl(true)) ?>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
        </div>

        <div class="col-12">
            <?php echo $file_input ?>
        </div>
    </div>
<?php else : ?>
    <?php echo $file_input; ?>
<?php endif;