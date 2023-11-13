<?php

use yii\web\View;







?>

<div class="alert alert-danger" role="alert">
    <p>
        <?= Yii::t(
            'sandbox/moderate-validation-errors/all',
            'Тело сообщения об ошибке валидации форм; на стр. проверки анкеты поступающего: `Ошибки валидации`'
        ) ?>:
    </p>

    <ul class="ml-0 pl-3">
        <?php foreach ($validationErrors as $name => $error) : ?>
            <?php if (is_string($name)) : ?>
                <p>
                    <strong>
                        <?= $name ?>
                    </strong>
                </p>
            <?php endif; ?>

            <?php foreach ($error as $key => $innerError) : ?>
                <li>
                    <?= $innerError[0]; ?>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</div>