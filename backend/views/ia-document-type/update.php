<?php
$this->title = 'Обновление типа скан-копии' . $model->name;

echo $this->render('_form', [
    'model' => $model,
    'document_types' => $document_types
]);
