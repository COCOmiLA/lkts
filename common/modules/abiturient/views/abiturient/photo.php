<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
?>
<div>
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data', 'name' => 'files'],
    ]);
    echo Html::fileInput('file');
    echo Html::submitInput('Отправить');
    ActiveForm::end();
    ?>
</div>
<div style="text-align: center;">
    <div>
            <img src="<?= \yii\helpers\Url::to(['/abiturient/get-photo']) ?>" alt="">
            <div>
                <a href="?act=in&percent=<?=$percent?>"
                   class="glyphicon glyphicon-zoom-in" style="font-size: 30px;"> </a>
                <a href="?act=out&percent=<?=$percent?>"
                   class="glyphicon glyphicon-zoom-out" style="font-size: 30px; margin-left: 20px;"> </a>
            </div>
    </div>
</div>
