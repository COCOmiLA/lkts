<?php




$this->title = "Создать новый нормативный документ";
$this->params['breadcrumbs'][] = ['label' => 'Нормативные документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', [
    'model' => $model,
]);
