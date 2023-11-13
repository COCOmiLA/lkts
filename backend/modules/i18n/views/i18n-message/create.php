<?php



$this->title = Yii::t('backend', 'Создание {modelClass}', [
    'modelClass' => 'I18n Message',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'I18N переводы'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="i18n-message-create">

    <?php echo $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
