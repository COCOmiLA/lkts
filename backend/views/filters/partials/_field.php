<?php

use backend\models\FiltersSetting;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use yii\base\DynamicModel;
use yii\web\View;









?>

<div class="card-body">
    <div class="row">
        <div class="col-8">
            <?= Yii::t('abiturient/filter-table', $filter->label) ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, "showColumn[$filter->name]")
                ->widget(SwitchInput::class, [])
                ->label(false) ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, "showFilter[$filter->name]")
                ->widget(SwitchInput::class, [])
                ->label(false) ?>
        </div>
    </div>
</div>