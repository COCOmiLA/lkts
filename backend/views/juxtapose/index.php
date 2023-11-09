<?php







use backend\models\search\UserDuplesSearchModel;
use common\models\ToAssocCaster;
use kartik\form\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Сопоставление';
?>
<div class="row card-body">
    <div class="col-12">
        <h2><?php echo "Сопоставление пользователя " . $user->getPublicIdentity() ?></h2>
        <?php if ($user->userRef) : ?>
            <?= Html::a(
                "<i class='fa fa-remove' aria-hidden='true'></i> Отвязать аккаунт от физического лица в Информационной системе вуза",
                ['/juxtapose/unbind-from-user', 'user_id' => $user->id],
                [
                    'class' => 'btn btn-danger',
                    'data-confirm' => 'Вы уверены, что хотите отвязать аккаунт от физического лица в Информационной системе вуза?',
                    'data-method' => 'post',
                ]
            ); ?>
        <?php endif; ?>
        <?php $form = ActiveForm::begin([
            'action' => ['/juxtapose/index', 'user_id' => $user->id],
            'method' => 'post',
        ]); ?>
        <?= $form->field($search_model, 'last_name'); ?>
        <?= $form->field($search_model, 'first_name'); ?>
        <?= $form->field($search_model, 'patronimyc'); ?>
        <?= $form->field($search_model, 'birth_date', ['inputOptions' => ['type' => 'date']]) ?>
        <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']); ?>
        <?php ActiveForm::end() ?>
    </div>
</div>

<div class="row card-body">
    <div class="col-12">
        <div class="found-duples">
            <?php if ($search_model->found_duples) : ?>
                <h2>Варианты физ. лиц для сопоставления:</h2>
                <?php $form = ActiveForm::begin(['action' => ['/juxtapose/bind-to-user', 'user_id' => $user->id], 'options' => ['method' => 'get']]); ?>
                <?php foreach ($search_model->found_duples as $infos) {
                    $assoc_infos = ToAssocCaster::getAssoc($infos);
                    $current_code = ArrayHelper::getValue($assoc_infos, 'EntrantRef.ReferenceId');
                ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label>
                                <?php echo Html::radio(
                                    'abit_code',
                                    $current_code == ($user->userRef->reference_id ?? null),
                                    ['value' => $current_code, 'required' => true]
                                );
                                ?>
                                Связать пользователя с физ. лицом
                                <strong> <?php echo $current_code ?><?php echo " - {$assoc_infos['LastName']} {$assoc_infos['FirstName']} {$assoc_infos['SecondName']} - {$assoc_infos['Birthdate']} - паспорт: {$assoc_infos['PassportSeries']} {$assoc_infos['PassportNumber']}" ?>
                            </label>
                        </div>
                    </div>
                <?php } ?>
                <?php
                echo Html::submitButton('Сопоставить', ['class' => 'btn btn-success']);
                ActiveForm::end();
                ?>
            <?php else : ?>
                <div class="alert alert-info" role="alert">
                    <p>По заданным параметрам ничего не найдено</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>