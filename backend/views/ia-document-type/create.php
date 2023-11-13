<?php

$this->title = 'Добавить тип скан-копии для индивидуального достижения';

echo $this->render('_form', [
    'model' => $model,
    'document_types' => $document_types,
    'campaigns' => $campaigns,
]);
