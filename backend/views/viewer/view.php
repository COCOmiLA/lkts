<?php

use yii\helpers\Html;

$this->title = $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Управление приемными кампаниями модератора', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-index">
    <p>
        <?= Html::a(Yii::t('backend', 'Редактировать'), ['update', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <div class="checkbox">
        <label>
            <?= Html::checkbox('', false, ['disabled' => 'true', 'value' => "all"]); ?>
            Выбрать все приемные кампании
        </label>
    </div>

    <hr />

    <?php foreach ($applicationTypes as $applicationType) : ?>
        <div class="checkbox">
            <?php if (in_array($applicationType->id, $admissionCampaignJunctions)) : ?>
                <label>
                    <?= Html::checkbox('', true, ['disabled' => 'true', 'value' => $applicationType->id]); ?>
                    <?= $applicationType->name; ?>
                </label>
            <?php else : ?>
                <label>
                    <?= Html::checkbox('', false, ['disabled' => 'true', 'value' =>  $applicationType->id]); ?>
                    <?= $applicationType->name; ?>
                </label>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>