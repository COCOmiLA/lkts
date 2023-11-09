<?php

use common\models\User;
use common\models\UserRegistrationConfirmToken;







?>
<div>
    <h4>
        <?= Yii::t(
            'abiturient/notifier/email-confirm',
            'Приветствие; для письма подтверждения электронной почты в менеджере оповещений: `Здравствуйте, {fio}!`',
            ['fio' => $user->getPublicIdentity()]
        ) ?>
    </h4>

    <p>
        <?= Yii::t(
            'abiturient/notifier/email-confirm',
            'Текст перед ссылкой на ЛК; для письма подтверждения электронной почты в менеджере оповещений: `Для завершения регистрации на портале`'
        ) ?>

        <a href="<?= Yii::$app->homeUrl; ?>">
            <?= Yii::$app->name; ?>
        </a>
    </p>

    <p>
        <?= Yii::t(
            'abiturient/notifier/email-confirm',
            'Текст о необходимости подтверждения почты; для письма подтверждения электронной почты в менеджере оповещений: `Необходимо подтвердить Email. Для подтверждения email вы можете:`'
        ) ?>
    </p>

    <ol>
        <li>
            <?= Yii::t(
                'abiturient/notifier/email-confirm',
                'Текст перед ссылкой для подтверждения почты; для письма подтверждения электронной почты в менеджере оповещений: `Перейти по ссылке для подтверждения email:`'
            ) ?>

            <em>
                <a href="<?= Yii::$app->homeUrl . $token->getUrlToConfirm(); ?>">

                    <?= Yii::t(
                        'abiturient/notifier/email-confirm',
                        'Подпись ссылки для подтверждения почты; для письма подтверждения электронной почты в менеджере оповещений: `Подтвердить email!`'
                    ) ?>
                </a>
            </em>
        </li>

        <li>
            <p>
                <?= Yii::t(
                    'abiturient/notifier/email-confirm',
                    'Текст перед кодом подтверждения почты; для письма подтверждения электронной почты в менеджере оповещений: `Ввести код для подтверждения email:`'
                ) ?>
            </p>

            <table>
                <tr>
                    <?php foreach (str_split($token->confirm_code) as $sym) : ?>
                        <td>
                            <?= $sym ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>
        </li>
    </ol>

    <p style="font-size: 10px">
        <em>
            <?= Yii::t(
                'abiturient/notifier/email-confirm',
                'Текст с указанием времени действия кода подтверждения почты; для письма подтверждения электронной почты в менеджере оповещений: `* код и ссылка для подтверждения email действуют {ttl} минут`',
                ['ttl' => $ttl]
            ) ?>
        </em>
    </p>
    <p>
        <b>
            <?= Yii::t(
                'abiturient/notifier/common',
                'Текст письма об отсутствии необходимости отвечать: `Пожалуйста, не отвечайте на это письмо, так как оно сгенерировано автоматически.`'
            ) ?>
        </b>
    </p>
</div>