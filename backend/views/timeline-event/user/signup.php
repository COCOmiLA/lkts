<?php




?>
<div class="timeline-item card-body">
    <span class="time">
        <i class="fa fa-clock-o"></i>
        <?php echo Yii::$app->formatter->asRelativeTime($model->created_at) ?>
    </span>

    <h3 class="timeline-header">
        <?php echo Yii::t('backend', 'У вас новый пользователь') ?>
    </h3>

    <div class="timeline-body">
        <?php echo Yii::t('backend', 'Новый пользователь ({identity}) был зарегистрирован {created_at}', [
            'identity' => $model->data['public_identity'],
            'created_at' => Yii::$app->formatter->asDatetime($model->data['created_at'])
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