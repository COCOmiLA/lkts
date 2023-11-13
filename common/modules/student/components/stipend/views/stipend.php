<?php





use common\modules\student\components\stipend\StipendWidget;
use kartik\widgets\Alert;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);

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
        <h3>Стипендии и прочие выплаты</h3>
        <?php echo StipendWidget::widget([
            'recordBook_id' => $recordBook_id
        ]); ?>
    </div>
</div>