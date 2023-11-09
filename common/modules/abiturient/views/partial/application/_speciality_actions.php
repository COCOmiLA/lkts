<?php

use common\modules\abiturient\assets\specialityActionsAsset\SpecialityActionsAsset;
use yii\helpers\Url;
use yii\web\View;



SpecialityActionsAsset::register($this);
?>

<?php if (isset($specialities) && $specialities) : ?>
    <?php $i = 1; ?>
    <?php foreach ($specialities as $key => $bachelor_speciality) : ?>
        <?php
        $is_remove_spec_revertable = $bachelor_speciality->isDeleteRevertable();
        $is_end_of_stage = !$is_remove_spec_revertable;
        if (\Yii::$app->user->identity->isModer()) {
            $canEdit = isset($isReadonly) && !$isReadonly;
        } else {
            $canEdit = $application->canEdit() && !$is_end_of_stage;
        }
        $is_enlisted = false;
        if ($bachelor_speciality->is_enlisted) {
            $is_enlisted = true;
            $canEdit = false;
        }
        ?>

        <?php if (!$is_enlisted) : ?>
            <?php $url = Url::toRoute(["bachelor/removespeciality", 'id' => $application->id]); ?>
            <form id="remove-<?= $bachelor_speciality->id; ?>" name="remove-<?= $bachelor_speciality->id; ?>" action="<?= $url; ?>" method="POST">
                <input type="hidden" name="id" value="<?= $bachelor_speciality->id; ?>" />

                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken(); ?>" />
            </form>

            <?php if ($canEdit && $i != 1) : ?>
                <?php $url = Url::toRoute(["bachelor/reorderspeciality", 'id' => $application->id]); ?>
                <form id="reorderup-<?= $bachelor_speciality->id; ?>" name="reorderup-<?= $bachelor_speciality->id; ?>" action="<?= $url; ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $bachelor_speciality->id; ?>" />

                    <input type="hidden" name="type" value="up" />

                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken(); ?>" />
                </form>
            <?php endif; ?>

            <?php if ($canEdit && $i != sizeof($specialities)) : ?>
                <?php $url = Url::toRoute(["bachelor/reorderspeciality", 'id' => $application->id]); ?>
                <form id="reorderdown-<?= $bachelor_speciality->id; ?>" name="reorderup-<?= $bachelor_speciality->id; ?>" action="<?= $url; ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $bachelor_speciality->id; ?>" />

                    <input type="hidden" name="type" value="down" />

                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken(); ?>" />
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php $i++; ?>
    <?php endforeach; ?>
<?php endif;
