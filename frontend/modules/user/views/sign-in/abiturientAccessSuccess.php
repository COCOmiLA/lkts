<?php

use yii\helpers\Html;





$this->title = 'Пароль создан успешно';
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="site-login">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="alert alert-success" role="alert">
        <p>Пароль создан успешно и отправлен на электронную почту <?php echo $email; ?></p>
    </div>
</div>
