<?php

use common\models\Attachment;
use kartik\widgets\FileInput;
use yii\helpers\Url;






$initialPreview = [];
$initialConfig = [];

foreach ($attachments as $attachment) {
    $initialPreview[] = Url::toRoute(['site/download', 'id' => $attachment->id]);
    $initialConfig[] = [
        'caption' => $attachment->file
    ];
}

?>

<div class="row">
    <div class="col-12 change-file">
        <?php
        echo FileInput::widget([
            'name' => "change_attachment_$key",
            'id' => "change_attachment_$key",
            'options' => [
                'multiple' => true
            ],
            'disabled' => true,
            'pluginOptions' => [
                'theme' => 'fa4',
                'initialPreview' => $initialPreview,
                'initialPreviewAsData' => true,
                'initialPreviewConfig' => $initialConfig,
                'overwriteInitial' => false,
                'maxFileSize' => 2800,
                'showPreview' => true,
                'showUpload' => false,
                'showRemove' => false,
                'showClose' => false,
            ]
        ]);
        ?>
    </div>
</div>
<style>
    .change-file .file-caption-main {
        display: none;
    }
</style>