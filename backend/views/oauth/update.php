<?php





$this->title = Yii::t('backend', 'Редактирование {modelClass}: ', ['modelClass' => 'Приложения OAuth2']) . ' ' . $model->client_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Приложения OAuth2'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('backend', 'Редактировать')];
?>
<div class="oauth-update card-body">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
