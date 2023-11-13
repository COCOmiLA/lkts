<?php




?>
<div class="timeline-item card-body">
    <span class="time">
        <i class="fa fa-clock-o"></i>
        <?php echo Yii::$app->formatter->asRelativeTime($model->created_at) ?>
    </span>
    <h3 class="timeline-header">
        <?php echo Yii::t('backend', 'У вас новое событие') ?>
    </h3>

    <div class="timeline-body">
        <dl>
            <dt><?php echo Yii::t('backend', 'Приложение') ?>:</dt>
            <dd><?php echo $model->application ?></dd>

            <dt><?php echo Yii::t('backend', 'Категория') ?>:</dt>
            <dd><?php echo $model->category ?></dd>

            <dt><?php echo Yii::t('backend', 'Событие') ?>:</dt>
            <dd><?php echo $model->event ?></dd>

            <dt><?php echo Yii::t('backend', 'Дата') ?>:</dt>
            <dd><?php echo Yii::$app->formatter->asDatetime($model->created_at) ?></dd>
        </dl>
    </div>
</div>