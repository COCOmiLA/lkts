<?php

use yii\web\View;







?>

<p class="collapse-header collapse-light mb-2 pt-2 pb-2" type="button" data-toggle="collapse" data-target="#additional_user_info" aria-expanded="false" aria-controls="collapseExample">
    <?= Yii::t(
        '_partial/chat/additional_user_info',
        'Подпись кнопки раскрытия доп. информации об пользователе, в блоке заголовка контакта: `Дополнительная информация`'
    ); ?>
</p>

<div class="collapse collapse-light" id="additional_user_info">
    <?php foreach ($additionalUserInfoList as $info) : ?>
        <p class="mb-1">
            <strong>
                <?= $info['label'] ?>:
            </strong>

            <?= $info['value'] ?>
        </p>
    <?php endforeach; ?>
</div>