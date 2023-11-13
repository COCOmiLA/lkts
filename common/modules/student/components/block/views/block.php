<?php


use common\modules\student\components\block\BlockWidget;

$this->title = Yii::$app->name;
?>
<div class="site-index">
    <div class="body-content">
        <?php 
        $alert = \Yii::$app->session->getFlash('ErrorSoapResponse');
        if (strlen((string)$alert) > 1) {
            echo '<div class="alert alert-danger" role="alert">';
            echo $alert;
            echo '</div>';
        } ?>
    </div>
    <div class="body-content">
        <h3>Запись на курсы по выбору</h3>
        <?php echo BlockWidget::widget(); ?>
    </div>
</div>
