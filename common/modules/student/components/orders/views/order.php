<?php



use common\modules\student\components\orders\OrdersWidget;

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
        <h3>Список приказов</h3>
        <?php echo OrdersWidget::widget(); ?>
    </div>

</div>
