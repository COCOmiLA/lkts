<?php

use common\assets\ImageInPopupViewerAsset;
use common\models\AttachmentType;
use common\models\AttachmentTypeTemplate;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii2assets\pdfjs\PdfJs;









ImageInPopupViewerAsset::register($this);

$fileUrl = $attachmentTypeTemplate->getDownloadUrl();
$modalLabel = ArrayHelper::getValue($attachmentTypeTemplate, 'linkedFile.upload_name', '');

$modalHeader = Yii::t('backend', 'Шаблон «{RelatedTitle}»', ['RelatedTitle' => $model->GetRelatedTitle()]);
Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'title' => $modalHeader,
    'toggleButton' => [
        'label' => $modalLabel,
        'class' => 'btn btn-link'
    ],
    'bodyOptions' => ['class' => 'd-flex justify-content-center'],
]);

if (strpos($modalLabel, '.pdf') !== false) {
    echo PdfJs::widget(['url' => $fileUrl]);
} else {
    echo Html::a(
        Html::tag(
            'span',
            Yii::t('backend', 'Скачать') . '<i class="pl-1 fa fa-download" aria-hidden="true"></i>',
            ['class' => 'pr-2 pb-2 pl-1 text-white']
        ) . Html::tag(
            'img',
            null,
            ['src' => $fileUrl, 'alt' => $modalHeader]
        ),
        $fileUrl,
        ['class' => 'template-img-view']
    );
}

Modal::end();
