<?php

use common\modules\student\components\schedule\ScheduleWidget;
use kartik\widgets\Alert;
use yii\web\View;



$this->title = Yii::$app->name;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);

?>
<div class="site-index">
    <div class="body-content">
        <?php 
        $alert = \Yii::$app->session->getFlash('ErrorSoapResponse'); ?>
        <?php if (strlen((string)$alert) > 1): ?>
            <div class="alert alert-danger" role="alert">
                <?= $alert; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="body-content">
        <h3>
            Расписание
        </h3>

        <?= ScheduleWidget::widget([
            'endDate' => $endDate,
            'objectId' => $objectId,
            'startDate' => $startDate,
            'class' => 'ScheduleWidget',
            'day_button' => $day_button,
            'objectType' => $objectType,
            'scheduleType' => $scheduleType,
            'another_group' => $another_group,
            'selected_position' => $selected_position,
        ]); ?>
    </div>
</div>