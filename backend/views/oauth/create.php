<?php



$this->title = Yii::t('backend', 'Создание {modelClass}', [
    'modelClass' => Yii::t('backend', 'OAuthClients'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'OAuthClients'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oauth-create card-body">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>