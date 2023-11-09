<?php

use yii\bootstrap4\Alert;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Настройка главной страницы';

?>

<?php if (empty($rolesList)) : ?>
    <?= Alert::widget([
        'options' => ['class' => 'alert-danger'],
        'body' => 'Отсутствуют страницы дя настойки отображаемых панелей',
    ]) ?>
<?php else : ?>
    <div class="rolerule-settings">
        <?php foreach ($rolesList as $role => $roleName) {
            $link = Html::a(
                'Настроить',
                Url::to(['page/index', 'role' => $role]),
                ['class' => 'btn btn-primary']
            );
            echo Html::tag(
                'p',
                "Настроить отображение элементов на главной у \"{$roleName}\" {$link}"
            );
        } ?>
    </div>
<?php endif;