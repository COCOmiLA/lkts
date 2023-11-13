<?php

use common\models\User;
use yii\helpers\Url;

$this->title = 'Выберите роль для входа';
$this->params['breadcrumbs'][] = $this->title;
$roles_translate = [
    'Student' => 'user',
    'Teacher' => 'graduation-cap',
    'Abiturient' => 'font'
];
?>

<div class="site-login">
    <h1>Выберите роль для входа:</h1>
    <div class="row">
        <div class="col-12">
            <div class="roles-container">
                <?php if (isset($roles) && is_array($roles) && $roles) : ?>
                    <?php foreach ($roles as $role) : ?>
                        <a href="<?php echo Url::toRoute(['sign-in/userset', 'role' => $role]); ?>">
                            <?php echo "<i class=\"fa fa-$roles_translate[$role]\" aria-hidden=\"true\"></i>"; ?>
                            <?php echo User::getRoleName($role); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="alert alert-warning">
                        Для данного пользователя нет доступных ролей
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>