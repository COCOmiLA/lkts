<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\CommentsComing;
use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;








$this->title = Yii::$app->name . ' | ' . Yii::t(
        'abiturient/bachelor/comment/all',
        'Заголовок страницы комментария: `Личный кабинет поступающего | Комментарий`'
    );

?>

<?= $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]);

?>

<?php $alert = Yii::$app->session->getFlash('comSaved');
if (strlen((string)$alert)) {
    echo Html::tag(
        'div',
        $alert,
        [
            'class' => 'alert alert-success',
            'role' => 'alert',
        ]
    );
} ?>

<?php $alert = Yii::$app->session->getFlash('comNotSaved');
if (strlen((string)$alert)) {
    echo Html::tag(
        'div',
        $alert,
        [
            'class' => 'alert alert-danger',
            'role' => 'alert',
        ]
    );
} ?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'comment')
    ->textarea([
        'rows' => '7',
        'style' => 'resize : none'
    ]) ?>

    <div class="form-group">
    <?= Html::submitButton(
        Yii::t(
            'abiturient/bachelor/comment/all',
            'Подпись кнопки сохранения формы комментария; на страницы комментария: `Сохранить`'
        ),
            ['class' => 'btn btn-primary float-right']
    ); ?>
    </div>

<?php ActiveForm::end();