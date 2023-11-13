<?php

use yii\helpers\Url;

$this->title = Yii::$app->name. ' | '.' Списки поступающих';
?>
<div class="col-12">
    <div>
        <h3 class="float-left">Списки поступающих</h3>
    </div>
</div>
<div class="mx-gutters">
    <div class="row">
        <div class="col-6"><a href="<?php echo Url::toRoute("admission/totallist"); ?>">Количество поступающих и поданных заявлений</a></div>
        <div class="col-6"><a href="<?php echo Url::toRoute("admission/chancelist"); ?>">Оцени свой шанс</a></div>
    </div>
    <div class="row">
        <div class="col-6"><a href="<?php echo Url::toRoute("admission/specialitylist"); ?>">Пофамильные перечни</a></div>
        <div class="col-6"><a href="<?php echo Url::toRoute("admission/competitionlist"); ?>">Конкурсные списки</a></div>
    </div>
</div>
