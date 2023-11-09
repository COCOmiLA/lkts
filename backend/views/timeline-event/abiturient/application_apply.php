<?php




?>
<div class="timeline-item card-body">
    <span class="time">
        <i class="fa fa-clock-o"></i>
        <?php echo Yii::$app->formatter->asRelativeTime($model->created_at) ?>
    </span>

    <h3 class="timeline-header">
        <?php echo Yii::t('backend', 'Подано заявление') ?>
    </h3>

    <div class="timeline-body">
        <?php echo Yii::t('backend', 'Поступающий ({identity}) подал заявление в ПК {campaign} {created_at}', [
            'identity' => $model->data['public_identity'],
            'campaign' => $model->data['campaign'],
            'created_at' => Yii::$app->formatter->asDatetime($model->created_at)
        ]) ?>
    </div>

    <div class="timeline-footer">
        <?php echo \yii\helpers\Html::a(
            Yii::t('backend', 'Просмотр пользователя'),
            ['/user/view', 'id' => $model->data['user_id']],
            ['class' => 'btn btn-success btn-sm']
        ) ?>
    </div>
</div>