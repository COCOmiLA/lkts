<?php



$this->title = Yii::t('backend', 'Создание {modelClass}', [
    'modelClass' => 'I18n Source Message',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Тексты'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="i18n-source-message-create">

    <?php echo $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
