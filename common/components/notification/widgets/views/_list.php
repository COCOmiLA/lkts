<?php

use common\models\notification\Notification;


?>

<?php foreach ($models as $model): ?>
    <?php echo $this->render('_notification', ['model' => $model]); ?>
<?php endforeach;