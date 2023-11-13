<?php

use common\components\ini\iniGet;
use yii\web\JqueryAsset;
use yii\web\View;







if (empty($formId)) {
    $formId = 'form';
}

$this->registerJsVar('formId', $formId, View::POS_END);

$uploadSizeLimit = iniGet::getUploadSizeLimitMultiple();
$maxFileUploads = iniGet::getMaximumFileUploadsNumber();
$this->registerJsVar('uploadSizeLimit', $uploadSizeLimit, View::POS_END);
$this->registerJsVar('maxFileUploads', $maxFileUploads, View::POS_END);

$this->registerJsFile(\common\helpers\Mix::mix('/js/file_size_validator.js'), ['depends' => JqueryAsset::class, 'position' => View::POS_END]);

?>

<div class="col-12 file-size-validator">
    <div class="alert alert-danger">
        <?= Yii::t(
            'abiturient/attachment-widget',
            'Текст информационного сообщения о максимальном размере файлов виджета сканов: `Общий объем прикрепляемых файлов не должен превышать {uploadSizeLimit} Мбайт.<br /> Объем каждого файла - не более {getUploadMaxFilesize} Мбайт.<br /> Максимальное количество загружаемых за раз файлов: {maxFileUploads} шт.`',
            [
                'maxFileUploads' => $maxFileUploads,
                'uploadSizeLimit' => floor($uploadSizeLimit / 1024 / 1024),
                'getUploadMaxFilesize' => floor(iniGet::getUploadMaxFilesize() / 1024),
            ]
        ) ?>
    </div>
</div>