<?php






$this->title = Yii::t('backend', 'Редактирование {modelClass}: ', ['modelClass' => 'User']) . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Пользователи'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->email, 'url' => ['view', 'id' => $model->model->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Редактировать')];

echo $this->render('_form', [
    'id' => $id,
    'model' => $model,
    'roles' => $roles
]);
