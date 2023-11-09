<?php

use backend\models\MainPageInstructionText;
use kartik\form\ActiveForm;
use yii\web\View;








$index = 'paragraph';
if ($model->main_page_setting_id) {
    $index = "[{$model->main_page_setting_id}]{$index}";
}

echo $form->field($model, $index)
    ->textarea();
