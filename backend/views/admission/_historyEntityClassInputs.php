<?php

use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use backend\models\applicationTypeHistory\ApplicationTypeHistoryEntityClassInput;
use yii\web\View;








$class = (new $history->change_class);

?>

<?php foreach ($historyEntityClassInputs as $classInput) : ?>
    <?php  ?>

    <div class="row">
        <div class="col-4">
            <?= $class->getAttributeLabel($classInput->input_name) ?>
        </div>

        <div class="col-4">
            <label>
                <?= Yii::t('backend', 'Текущее значение:') ?>
            </label>

            <?= $classInput->renderHumanActualValue() ?>
        </div>

        <div class="col-4">
            <label>
                <?= Yii::t('backend', 'Старое значение:') ?>
            </label>

            <?= $classInput->renderHumanOldValue() ?>
        </div>
    </div>
<?php endforeach;