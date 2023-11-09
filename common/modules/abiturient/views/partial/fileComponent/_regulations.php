<?php

use common\models\UserRegulation;
use common\modules\abiturient\models\repositories\FileRepository;
use yii\web\View;
use kartik\form\ActiveForm;









FileRepository::SortCollection($regulations);
?>

<div class="col-12">
    <h3>
        <?= Yii::t(
            'abiturient/attachment-widget',
            'Заголовок блока нормативных документов виджета сканов: `Нормативные документы`'
        ) ?>
    </h3>
</div>

<div class="col-12 mb-3">
    <?php foreach ($regulations as $key => $regulation) : ?>
        <?= $this->render('_regulation', [
            'regulation' => $regulation,
            'isReadonly' => $isReadonly,
            'form' => $form ?? null
        ]); ?>
    <?php endforeach; ?>
</div>