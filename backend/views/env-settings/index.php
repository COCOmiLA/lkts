<?php

use yii\bootstrap4\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Настройка переменных окружения');

?>

<?php if (!empty($missingEnvironmentSettings)) : ?>
    <div class="alert alert-danger">
        <?= Yii::t('backend', 'Не все параметры окружения заполнены!') ?>
    </div>

    <div class="alert alert-info">
        <?= Yii::t('backend', 'Перечень отсутствующих параметров:') ?>

        <ul>
            <li>
                <?= implode('</li><li>', $missingEnvironmentSettings); ?>
            </li>
        </ul>
    </div>

    <?= Html::a(Yii::t('backend', 'Заполнить параметры окружения'), ['fill-env-variables'], ['class' => 'btn btn-primary']) ?>
<?php else : ?>
    <div class="alert alert-success">
        <?= Yii::t('backend', 'Все параметры окружения заполнены!') ?>
    </div>
<?php endif;