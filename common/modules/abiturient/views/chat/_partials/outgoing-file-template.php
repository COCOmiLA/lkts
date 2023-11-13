<?php

use yii\bootstrap4\Html;
use yii\web\View;











$file = '';

if (isset($fileDownloadUrl)) {
    $file = Html::a($fileName, $fileDownloadUrl, ['target' => '_blank', 'id' => "file-{$fileUid}"]);
} else {
    $file = Html::tag('span', $fileName, ['id' => "file-{$fileUid}"]);
}

?>

<li id="<?= $fileUid ?>">
    <div class="message-data">
        <span class="message-data-name">
            <?= $nickname ?>
        </span>

        <span class="message-data-time">
            <?= $time ?>
        </span>
    </div>

    <div class="message incoming-message p-3 mt-2">
        <label class="file-blob" for="<?= "file-{$fileUid}" ?>">
            <i class="fa fa-file-o"></i>

            <?= $file ?>
        </label>
    </div>
</li>