<?php

use common\models\dictionary\DocumentType;
use common\models\Recaptcha;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use frontend\modules\user\models\AccessForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;





$appLanguage = Yii::$app->language;

$this->title = 'Восстановление доступа';
$this->params['breadcrumbs'][] = $this->title;


$login_label = "Электронная почта";
$alert = Yii::$app->session->getFlash('abiturientCodeExt_ErrorValidateEmail');
if (strlen((string)$alert) > 0) {
    echo Html::tag('div', $alert, ['class' => 'alert alert-danger', 'role' => 'alert']);
}
?>
<div class="site-login">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <?php if ($model->error_code == AccessForm::NO_ERROR) : ?>
        <div class="alert alert-success" role="alert">
            <?php if ($recoverModel != null && $model->possibleEmail != null) : ?>
                <p style="text-align: justify; -moz-text-align-last: justify;text-align-last: justify;">У вас есть
                    доступ к почте <strong> <?= $model->hiddenEmail ?></strong>? При подтверждении на этот адрес будет отправлено
                    письмо для восстановления пароля.</p>
                <div class="d-flex justify-content-end align-items-center">
                    <?php $recoverForm = ActiveForm::begin(['id' => 'request-password-reset-form', 'action' => Url::toRoute(['/user/sign-in/request-password-reset'])]); ?>
                    <?php echo $recoverForm->field($recoverModel, 'email')->label(false)->hiddenInput() ?>
                    <div class="form-group">
                        <?php echo Html::submitButton("Да, у меня есть доступ", ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <a class="btn btn-danger" style="margin-left: 20px;" href="<?= Url::toRoute(['/user/sign-in/abiturient-recover']) ?>">
                        Нет, у меня нет доступа.
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($accessTopText = Yii::$app->configurationManager->getText('access_top_text')) : ?>
        <div class="alert alert-info" role="alert">
            <?php echo $accessTopText; ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['id' => 'access-form']); ?>
    <div class="row">
        <div class="col-sm-6 col-12">
            <?php echo Html::hiddenInput('vt', (int)Yii::$app->configurationManager->signupEmailEnabled, ['id' => 'vt']); ?>
            <?php echo $form->field($model, 'lastname')->textInput() ?>
            <?php echo $form->field($model, 'firstname')->textInput() ?>
            <?php echo $form->field($model, 'secondname')->textInput() ?>
        </div>
        <div class="col-sm-6 col-12">
            <?php
            $uid = Yii::$app->configurationManager->getCode('identity_docs_guid');
            $parent = DocumentType::findByUID($uid);
            if ($parent) {
                $docs = DocumentType::find()->notMarkedToDelete()->active()->andWhere(['parent_key' => $parent->ref_key])->orderBy(['ref_key' => SORT_DESC])->all();
            } else {
                $docs = [];
            }
            echo $form->field($model, 'documentTypeId')->dropDownList(ArrayHelper::map($docs, 'id', 'description'), [
                'id' => 'doc_type'
            ]) ?>
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <?php echo $form->field($model, 'passportSeries')->textInput() ?>
                </div>
                <div class="col-md-6 col-sm-12">
                    <?php echo $form->field($model, 'passportNumber')->textInput() ?>
                </div>
            </div>
            <?php echo $form->field($model, 'birth_date')->widget(
                DatePickerMaskedWidget::class,
                [
                    'inline' => false,
                    'language' => $appLanguage,
                    'template' => '{input}{addon}',
                    'clientOptions' => [
                        'clearBtn' => true,
                        'weekStart' => '1',
                        'autoclose' => true,
                        'endDate' => '-1d',
                        'todayBtn' => 'linked',
                        'format' => 'dd.mm.yyyy',
                        'calendarWeeks' => 'true',
                        'todayHighlight' => 'true',
                        'orientation' => 'top left',
                    ],
                    'options' => [
                        'autocomplete' => 'off',
                        'id' => "passportdata-issued_date",
                    ],

                    'maskOptions' => [
                        'alias' => 'dd.mm.yyyy'
                    ]
                ]
            ); ?>

            <?php $widgetParams = Recaptcha::getWidgetParamsByName('abit_access'); ?>
            <?php if (!empty($widgetParams)) : ?>
                <?= $form->field(
                    $model,
                    'reCaptcha',
                    ['template' => '{input}']
                )->widget(
                    $widgetParams['class'],
                    $widgetParams['settings']
                ); ?>
            <?php endif; ?>

        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-12">
            <?php if (!Yii::$app->configurationManager->signupEmailEnabled) : ?>
            <?php endif; ?>
            <div class="form-group">
                <?php echo Html::submitButton('Создать пароль', ['class' => 'btn btn-primary float-right', 'name' => 'access-button']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <?php if ($accessBottomText = Yii::$app->configurationManager->getText('access_bottom_text')) : ?>
        <div style="height: 15px; clear:both"></div>
        <div class="alert alert-info" role="alert">
            <?php echo $accessBottomText; ?>
        </div>
    <?php endif; ?>
</div>