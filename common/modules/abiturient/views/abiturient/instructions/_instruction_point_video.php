<?php

use backend\models\MainPageInstructionVideo;
use yii\web\View;







?>

<video
    controls
    width="<?= $instruction->width ?>"
    height="<?= $instruction->height ?>"
    <?= $instruction->buildAdditionalHtmlAttributes() ?>
>
    <source
        src="<?= $instruction->buildSourceUrl() ?>"
        type="video/<?= $instruction->extensions ?>"
    >

    <?= Yii::t(
        'abiturient/download-instruction-attachment',
        'Текст сообщения о невозможности воспроизвести видео для инструкции поступающего: `Ваш браузер не поддерживает воспроизведение видео.`'
    ) ?>
</video>