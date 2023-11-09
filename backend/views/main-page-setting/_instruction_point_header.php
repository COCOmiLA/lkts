<?php

use backend\models\MainPageInstructionHeader;
use kartik\form\ActiveForm;
use yii\web\View;








$index = 'header';
if ($model->main_page_setting_id) {
    $index = "[{$model->main_page_setting_id}]{$index}";
}

echo $form->field($model, $index);
