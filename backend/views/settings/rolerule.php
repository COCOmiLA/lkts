<?php

use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Настройки доступа';

?>

<?php if ($rolerule_error) : ?>
    <?php echo Html::tag('div', "Используемая вами база данных устарела; корректная работа функции reCAPTCHA невозможна.
        Для устранения, необходимо произвести обновления на странице <a href=" . Url::to(['update/index']) . ">Настройки личного кабинета поступающего → Обновление</a>", ['class' => 'alert alert-danger']); ?>
<?php else : ?>
    <?php $form = ActiveForm::begin(); ?>
        <?php if (!$isAbit) {
            echo $form->field($model, 'student')->widget(SwitchInput::class, []);
            echo $form->field($model, 'teacher')->widget(SwitchInput::class, []);
        }
        echo $form->field($model, 'abiturient')->widget(SwitchInput::class, []); ?>

        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
    <?php ActiveForm::end() ?>
<?php endif;