<?php

use backend\models\ManagerAllowChat;
use yii\helpers\Url;
use yii\web\View;





?>

<?php if (ManagerAllowChat::isAllowChat()) : ?>
    <a class="btn btn-success float-right ml-1" href="<?php echo Url::to(['/manager-chat/manager-index']) ?>">
        <?php echo Yii::t(
            'sandbox/index/all',
            'Подпись кнопки перехода к чату; на стр. поданных заявлений: `Чат с поступающими`'
        ); ?>
    </a>
<?php endif; ?>
<a class="btn btn-primary float-right" href="<?php echo Url::to(['/notification']) ?>">
    <?php echo Yii::t(
        'sandbox/index/all',
        'Подпись кнопки перехода к рассылке уведомлений; на стр. поданных заявлений: `Рассылка уведомлений`'
    ); ?>
    <i class="fa fa-bell"></i>
</a>