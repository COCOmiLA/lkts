<?php

use common\modules\student\components\grade\GradeWidget;


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
        <h3>Сведения об успеваемости</h3>
        <?php echo GradeWidget::widget(); ?>
    </div>
</div>
