<?php





$this->title = Yii::t('backend', 'Редактирование {modelClass}: ', [
    'modelClass' => 'I18n Message',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'I18N переводы'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id, 'language' => $model->language]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Редактировать');
?>
<div class="i18n-message-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
