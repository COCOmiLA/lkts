<?php

use common\components\tree\treeWidget\TreeWidget;
use yii\web\View;



$this->title = Yii::$app->name;

?>

<div class="site-index">
    <div class="body-content">
        <?= TreeWidget::widget([
            'class' => 'ScheduleWidget',
            'treeArray' => $treeArray
        ]); ?>
    </div>
</div>
