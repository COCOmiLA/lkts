<?php

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\views\bachelor\assets\ApplicationChangeHistoryAsset;
use yii\web\View;







ApplicationChangeHistoryAsset::register($this);

?>

<div class="change-history-ajax">
    <?php foreach ($change_history as $history_row) : ?>
        <?= $this->render(
            '_applicationChangeHistoryOneNode',
            ['historyRow' => $history_row]
        ) ?>
    <?php endforeach; ?>
</div>