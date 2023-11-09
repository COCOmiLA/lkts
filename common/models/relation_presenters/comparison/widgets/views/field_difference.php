<?php

use yii\web\View;
use yii\widgets\ActiveField;








if (mb_strpos($field->template, '{label}') === false) {
    $field->template = "{label}{$field->template}";
}

$field->template = str_replace('{label}', "{label}{$difference}", $field->template);
if (!isset($field->parts['{label}'])) {
    $field->label($field->model->getAttributeLabel($field->attribute) . ':'); 
}

?>

<div class="row <?= $difference ? $difference_class : '' ?>">
    <div class="col-12 col-md-4 text-md-right">
        <?= $field->parts['{label}'] ?>
    </div>

    <div class="col-12 col-md-8">
        <?= $field->label(false) ?>
    </div>
</div>