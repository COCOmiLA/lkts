<?php

use yii\helpers\Html;

$this->title = $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Управление приемными кампаниями модератора', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
    <?php echo Html::a(Yii::t('backend', 'Редактировать'), ['update', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
</p>

<div class="form-check">
    <label>
        <?= Html::checkbox('', false, ['disabled' => 'true', 'value' => "all"]); ?>
        Выбрать все приемные кампании
    </label>
</div>

<hr />

<?php foreach ($application_type as $at) : ?>
    <div class="form-check">
        <?php if (in_array($at->id, $array_manger_ac)) : ?>
            <label>
                <?= Html::checkbox('', true, ['disabled' => 'true', 'value' => $at->id]); ?>
                <?php echo Html::encode($at->name); ?>
            </label>
        <?php else : ?>
            <label>
                <?= Html::checkbox('', false, ['disabled' => 'true', 'value' =>  $at->id]); ?>
                <?php echo Html::encode($at->name); ?>
            </label>
        <?php endif; ?>
    </div>
<?php endforeach;