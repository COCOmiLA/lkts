<?php

use yii\web\View;










?>

<li id="<?= $messageUid ?>">
    <div class="message-data">
        <span class="message-data-name">
            <?= $nickname ?>
        </span>

        <span class="message-data-time">
            <?= $time ?>
        </span>
    </div>

    <div class="message incoming-message p-3 mt-2">
        <?= $messageOutput ?>
    </div>
</li>