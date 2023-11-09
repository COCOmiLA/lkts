<?php

use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use yii\helpers\ArrayHelper;
use yii\web\View;







?>

<div class="row">
    <div class="col-12 col-md-3">
        <?= $centralizedTesting->getAttributeLabel('passed_subject_ref_id') ?>

        <br />

        <div class="help_block_leveler">
            "<?= ArrayHelper::getValue($centralizedTesting, 'passedSubjectRef.reference_name') ?? '-' ?>"
        </div>
    </div>

    <div class="col-12 col-md-3">
        <?= $centralizedTesting->getAttributeLabel('series') ?>

        <br />

        <div class="help_block_leveler">
            "<?= ArrayHelper::getValue($centralizedTesting, 'series') ?? '-' ?>"
        </div>
    </div>

    <div class="col-12 col-md-2">
        <?= $centralizedTesting->getAttributeLabel('number') ?>

        <br />

        <div class="help_block_leveler">
            "<?= ArrayHelper::getValue($centralizedTesting, 'number') ?? '-' ?>"
        </div>
    </div>

    <div class="col-12 col-md-2">
        <?= $centralizedTesting->getAttributeLabel('year') ?>

        <br />

        <div class="help_block_leveler">
            "<?= ArrayHelper::getValue($centralizedTesting, 'year') ?? '-' ?>"
        </div>
    </div>

    <div class="col-12 col-md-2">
        <?= $centralizedTesting->getAttributeLabel('mark') ?>

        <br />

        <div class="help_block_leveler">
            "<?= ArrayHelper::getValue($centralizedTesting, 'mark') ?? '-' ?>"
        </div>
    </div>
</div>