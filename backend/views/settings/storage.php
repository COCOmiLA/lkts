<?php

use backend\models\StorageDictionary;
use yii\bootstrap4\Alert;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;






$pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/RecaptchaLogo.svg');

$this->registerJs(
    '
        $(function () {
            $(\'[data-toggle="tooltip"]\').tooltip()
        })
    ',
    View::POS_READY,
    'tooltip_enabler'
);

$this->title = 'Настройка хранилища';
?>

<?php if ($hasError) : ?>
    <?= Alert::widget([
        'body' => 'Произошла ошибка в работе сайта. Обратитесь к администратору.',
        'options' => ['class' => 'alert-danger']
    ]); ?>
<?php else : ?>
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <?= $form->field($model, 'storagePath')
                ->textInput([
                    'data-html' => 'true',
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'title' => "
                            Укажите абсолютный путь до сетевого хранилища.
                            <br/>
                            Чтобы сбросить путь к хранилищу файлов до заводских настроек (папка <strong>storage</strong> в корне портала)
                            необходимо оставить поле <em>'{$model->getAttributeLabel('storagePath')}'</em> пустым.
                        ",
                ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
<?php endif;
