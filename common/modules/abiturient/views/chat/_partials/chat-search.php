<?php

use common\modules\abiturient\models\chat\ChatSearchModel;
use yii\bootstrap4\Html;
use yii\web\View;







if (!isset($searchModel)) {
    return;
}

$searchAttributes = array_keys($searchModel->attributes);

?>

<div class="search mb-2">
    <p class="collapse-header collapse-dark pt-2 pb-2" type="button" data-toggle="collapse" data-target="#search_user_info" aria-expanded="false" aria-controls="collapseExample">
        <?= Yii::t(
            'abiturient/chat/search',
            'Подпись кнопки разворачивания блока фильтров, в блоке списка контактов: `Поиск`'
        ); ?>
    </p>

    <div id="search_user_info" class="collapse collapse-bark">
        <form class="search-form" action="">
            <?php foreach ($searchAttributes as $attr) : ?>
                <?= $searchModel->renderFieldForm($attr); ?>
            <?php endforeach; ?>

            <?= Html::button(
                Yii::t(
                    'abiturient/chat/search',
                    'Подпись кнопки применения фильтров, в блоке списка контактов: `Отфильтровать`'
                ),
                [
                    'id' => 'accept-filters',
                    'class' => 'btn btn-sm btn-success',
                ]
            ); ?>

            <?= Html::button(
                Yii::t(
                    'abiturient/chat/search',
                    'Подпись кнопки сброса фильтров, в блоке списка контактов: `Сбросить`'
                ),
                [
                    'id' => 'clear-filters',
                    'class' => 'btn btn-sm btn-light float-right',
                ]
            ); ?>
        </form>
    </div>
</div>