<?php

use yii\bootstrap4\Html;
use yii\web\View;





 $url = Yii::$app->request->referrer ?: Yii::$app->homeUrl;

?>

<div class="row navigation-btn mb-2">
    <div class="col-12">
        <?= Html::a(
            Yii::t(
                'abiturient/chat/navigation-btn',
                'Подпись кнопки "назад", на странице чата: `Назад`'
            ),
            $url,
            ['class' => 'btn btn-info']
        ) ?>
    </div>
</div>