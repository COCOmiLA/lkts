<?php
$this->title = 'Добавить тип скан-копии';

echo $this->render('_form', [
    'model' => $model,
    'entities' => $entities,
    'document_types' => $document_types
]);
