<?php

use backend\models\MainPageInstructionHeader;
use yii\web\View;









?>

<span class="instruction-header-count">
    <?= $headerCount ?>
</span>

<h4 class="mb-0 mt-2">
    <?= strtr($instruction->header, ['{MAX_COUNT}' => $maxCount]) ?>
</h4>