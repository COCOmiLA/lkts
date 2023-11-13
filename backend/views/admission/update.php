<?php
$this->title = 'Обновление приемной кампании (сопоставления 1С) ' . $model->name;

?>

<?php echo $this->render('_form', [
    'model' => $model,
    'campaigns' => $campaigns
]);
