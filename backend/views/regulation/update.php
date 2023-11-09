<?php




$this->title = 'Изменить нормативный документ: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Нормативные документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменить';

echo $this->render('_form', [
    'model' => $model,
]);
