<?php





$this->title = Yii::t('backend', 'Редактирование {modelClass}: ', [
    'modelClass' => 'I18n Source Message',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Тексты'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Редактировать');
?>
<div class="i18n-source-message-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
