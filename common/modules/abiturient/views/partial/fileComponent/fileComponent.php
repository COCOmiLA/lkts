<?php

use yii\web\View;












$multiple = $multiple ?? true;

?>

<?php if ($showAttachments && $attachmentConfig['items']) : ?>
    <div class="row">
        <?= $this->render(
            '_attachments',
            [
                'app' => $attachmentConfig['application'] ?? null,
                'attachments' => $attachmentConfig['items'],
                'isReadonly' => $attachmentConfig['isReadonly'],
                'multiple' => $multiple
            ]
        ); ?>
    </div>
<?php endif; ?>

<?php if ($showRegulations && $regulationConfig['items']) : ?>
    <div class="row">
        <?= $this->render(
            '_regulations',
            [
                'isReadonly' => $regulationConfig['isReadonly'],
                'regulations' => $regulationConfig['items'],
                'form' =>  $regulationConfig['form'] ?? null,
            ]
        ); ?>
    </div>
<?php endif; ?>

<?php if (!$disableFileSizeValidation) : ?>
    <div class="row">
        <?= $this->render(
            '@common/view/_file_size_validator',
            ['formId' => $formId]
        ); ?>
    </div>
<?php endif;