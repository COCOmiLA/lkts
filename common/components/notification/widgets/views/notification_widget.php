<?php

use yii\data\ActiveDataProvider;


?>

<ul class="notification-drop">
    <li class="item">
        <i class="fa fa-bell-o notification-bell" aria-hidden="true"></i> 
        <span style="display: none" class="btn__badge pulse-button" id="uread_notifications_count"><?php echo $unread_count ?></span>
    </li>
    <div class="notification-list list-group" id="notification_list_container">
        <?php echo $this->render('_list', ['models' => $data_provider->getModels()]); ?>
    </div>
</ul>

<?php
$this->registerJsVar('unreadCount', $unread_count);
