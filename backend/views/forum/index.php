<?php

use kartik\widgets\SwitchInput;
use yii\base\DynamicModel;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;








$this->title = 'Форум';
$this->params['breadcrumbs'][] = $this->title;

?>

<table class="table">
    <?php if (!$installed) : ?>
        <tr>
            <td style="width:30%; vertical-align: middle;">
                Установить Форум:
            </td>
            <td>
                <?= Html::a('Установить', Url::toRoute('/podium/install'), ['class' => 'btn btn-primary']); ?>
            </td>
        </tr>
    <?php else : ?>
        <tr>
            <td>
                <?= Html::a('Войти в форум', str_replace(Url::base(), '', Url::to(['podium/forum'])), ['class' => 'btn btn-success']); ?>
            </td>
        </tr>
    <?php endif; ?>

    <tr>
        <td>
            <?php if (!$modelIsEmpty) : ?>
                <?php if (!empty($modelFields)) : ?>
                    <?php $form = ActiveForm::begin(['method' => 'post']); ?>
                    <!-- ### Начало формы ### -->
                    <?php foreach ($modelFields as $field) : ?>
                        <?= $form->field($forumModel,  $field)->widget(SwitchInput::class, []) ?>
                    <?php endforeach; ?>

                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
                    <!-- ### Конец формы ### -->
                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
</table>