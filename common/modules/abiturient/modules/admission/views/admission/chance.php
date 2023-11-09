<?php

use yii\web\View;

if ($code != null) {
    $script = '
            $(document).ready(function(){
                $("html, body").animate({
                                scrollTop: $("#' . $code . '").offset().top
                            }, 500);
            });
                ';
    $this->registerJs($script, View::POS_END);
}

$this->title = Yii::$app->name . ' | ' . 'Узнай свой шанс';
?>

<div class="mx-gutters abitlist-header">
    <h3>Узнай свой шанс</h3>
</div>
<div class="mx-gutters abitlist-filter">
</div>
<div class="mx-gutters abitlist-container">
    <?php echo $this->render('_chance',
        ['data' => $data, 'code' => $code]
    ); ?>
</div>