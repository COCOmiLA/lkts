<?php

use yii\bootstrap4\Html;
use yii\web\View;












$file = '';

if (isset($fileDownloadUrl)) {
    $file = Html::a($fileName, $fileDownloadUrl, ['target' => '_blank', 'id' => "file-{$fileUid}"]);
} else {
    $file = Html::tag('span', $fileName, ['id' => "file-{$fileUid}"]);
}

$status = $status ?? 'sending';

?>

<li class="d-flex flex-column mb-2 align-items-end" id="<?= $fileUid ?>">
    <div class="message-data float-right">
        <i class="message-status align-self-center <?= $status ?>"></i>

        <span class="message-data-time">
            <?= $time ?>
        </span>

        <span class="message-data-name pl-2">
            <?= $nickname ?>
        </span>
    </div>

    <div class="message outgoing-message float-right p-3 mt-2">
        <label class="file-blob" for="<?= "file-{$fileUid}" ?>">
            <i class="fa fa-file-o"></i>

            <?= $file ?>
        </label>
    </div>
</li>