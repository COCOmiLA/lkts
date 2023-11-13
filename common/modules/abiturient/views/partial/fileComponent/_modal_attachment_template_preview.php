<?php

use common\assets\ImageInPopupViewerAsset;
use common\models\AttachmentTypeTemplate;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\web\View;








ImageInPopupViewerAsset::register($this);

$url = $attachmentTypeTemplate->getDownloadUrl();
$modalHeader = "Шаблон «{$attachmentTypeLabel}»";
$linkedFileUploadName = ArrayHelper::getValue($attachmentTypeTemplate, 'linkedFile.upload_name', '');

?>

<?php Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'title' => $modalHeader,
    'toggleButton' => [
        'label' => $attachmentTypeLabel,
        'class' => 'btn btn-link'
    ],
    'bodyOptions' => ['class' => 'd-flex justify-content-center'],
]); ?>

<?php if (strpos($linkedFileUploadName, '.pdf') !== false) : ?>
    <iframe src="<?= $url; ?>" width="100%" height="500hv"></iframe>
<?php else : ?>
    <a class="template-img-view" href="<?= $url ?>" download>
        <span class="pr-2 pb-2 pl-1 text-white">
            <?= Yii::t(
                'abiturient/attachment-widget',
                'Текст для скачивания шаблона, в модальном окне шаблонов виджета сканов: `Скачать`'
            ); ?>

            <i class="pl-1 fa fa-download" aria-hidden="true"></i>
        </span>

        <img src="<?= $url ?>" alt="<?= $modalHeader ?>">
    </a>
<?php endif; ?>

<?php Modal::end();
