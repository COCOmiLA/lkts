<?php

use yii\web\View;











$status = $status ?? 'sending';

?>

<li class="d-flex flex-column mb-2 align-items-end" id="<?= $messageUid ?>">
    <div class="message-data d-flex flex-row justify-content-end">
        <i class="message-status align-self-center <?= $status ?>"></i>

        <span class="message-data-time">
            <?= $time ?>
        </span>

        <span class="message-data-name pl-2">
            <?= $nickname ?>
        </span>
    </div>

    <div class="message outgoing-message p-3 mt-2">
        <?= $messageOutput ?>
    </div>
</li>