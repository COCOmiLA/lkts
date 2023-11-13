<?php

use common\modules\abiturient\models\chat\ChatFileBase;
use yii\web\View;





?>

<div class="chat-file-uploader" id="chat-file-uploader-for-history">
    <input type="file" name="chat_file" id="file-upload" multiple data-allowed_extensions="<?= ChatFileBase::getExtensionsListForJs() ?>" />

    <label class="fa fa-file-o" for="file-upload" id="label-for-file-upload">
        <span class="upload-file-name"></span>

        <button class="fa fa-times remove-btn"></button>
    </label>

    <p>
        <?= Yii::t(
            '_partial/chat/chat_file_uploader',
            'Текст ошибки если пользователь прикрепил файл с недопустимым расширением, на виджете прикрепления файлов: `Вы прикрепили файл с недопустимым расширением. Разрешены файлы с расширениями: {extensions}`',
            ['extensions' => ChatFileBase::getExtensionsListForRules()]
        ); ?>
    </p>
</div>