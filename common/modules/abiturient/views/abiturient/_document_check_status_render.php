<?php

use common\models\interfaces\IHaveDocumentCheckStatus;
use yii\web\View;







?>

<div class="alert alert-primary alert-dismissible fade show" role="alert">
    <strong>
        <?= $model->getAttributeLabel('documentCheckStatus') ?>:
    </strong>

    <?= $model->documentCheckStatus ?>

    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>