<?php

use backend\models\MainPageInstructionText;
use yii\web\View;








?>

<p class="mb-0">
    <?= strtr($instruction->paragraph, ['{MAX_COUNT}' => $maxCount]) ?>
</p>