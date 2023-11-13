<?php

use common\models\dictionary\Country;
use common\models\dictionary\Gender;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\helpers\ArrayHelper;
use yii\web\View;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;







$appLanguage = Yii::$app->language;

?>
<!-- Обёрнут в row -->
<div class="col-12">
    <div class="card mb-3">
        <div class="card-header">
            <h4>
                <?= Yii::t(
                    'abiturient/questionary/parent-modal',
                    'Заголовок блока "Основные данные родителя" модального окна на странице анкеты поступающего: `Основные данные`'
                ); ?>
            </h4>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group required">
                        <?= $form->field($model, 'type_id')->dropDownList(ArrayHelper::map($familyTypes, 'id', 'name'), [
                            'template' => "{label}{input}\n{error}",
                            'class' => 'form-control',
                            'readonly' => $isReadonly,
                            $disabled => $disabled,
                            'prompt' => Yii::t(
                                'abiturient/questionary/parent-modal',
                                'Подпись пустого значения выпадающего списка для поля "type_id" формы родителя на странице анкеты поступающего: `Выберите ...`'
                            )
                        ]); ?>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group required">
                        <?= $form->field($model, 'email', ['template' => "{label}{input}\n{error}"])
                            ->textInput([
                                'type' => 'email',
                                'readonly' => $isReadonly, $disabled => $disabled
                            ]); ?>
                    </div>

                    <div class="form-group required">
                        <?= $form->field($personal_data, 'lastname', ['template' => "{label}{input}\n{error}"])
                            ->textInput(['readonly' => $isReadonly, $disabled => $disabled]); ?>
                    </div>

                    <div class="form-group required">
                        <?= $form->field($personal_data, 'firstname', ['template' => "{label}{input}\n{error}"])
                            ->textInput(['readonly' => $isReadonly, $disabled => $disabled]); ?>
                    </div>

                    <div class="form-group">
                        <?= $form->field($personal_data, 'middlename', ['template' => "{label}{input}\n{error}"])
                            ->textInput(['readonly' => $isReadonly, $disabled => $disabled]); ?>
                    </div>

                    <div class="form-group required">
                        <div class="gender-wrapper d-flex" style="white-space: nowrap;">
                            <label class="col-form-label has-star" style="padding: 0">
                                <?= $personal_data->getAttributeLabel('gender_id'); ?>
                            </label>

                            <?= $form->field($personal_data, 'gender_id', [
                                'template' => $template,
                                'options' => [
                                    'style' => 'width: 100%; text-align:center;'
                                ]
                            ])->radioList(
                                ArrayHelper::map(
                                    Gender::find()
                                        ->notMarkedToDelete()->active()
                                        ->orFilterWhere([Gender::tableName() . '.id' => $personal_data->gender_id])
                                        ->orderBy('code')->all(),
                                    'id',
                                    'description'
                                ),
                                [
                                    'item' => function ($index, $label, $name, $checked, $value) use ($disabled) {
                                        $str_checked = '';
                                        if ($checked == 1) {
                                            $str_checked = 'checked';
                                        }
                                        $return = '<div class="col-md-6 col-12">';
                                        $return .= '<label class="modal-radio">';
                                        $return .= "<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$str_checked} {$disabled}>";
                                        $return .= '<em></em>';
                                        $return .= '<span>' . ucwords($label) . '</span>';
                                        $return .= '</label>';
                                        $return .= '</div>';
                                        return $return;
                                    },
                                    $disabled => $disabled
                                ]
                            ); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <?= $form->field($personal_data, 'birthdate')->widget(
                            DatePickerMaskedWidget::class,
                            [
                                'inline' => false,
                                'language' => $appLanguage,
                                'template' => "{input}{addon}",
                                'clientOptions' => [
                                    'clearBtn' => true,
                                    'weekStart' => '1',
                                    'autoclose' => true,
                                    'todayBtn' => 'linked',
                                    'format' => 'dd.mm.yyyy',
                                    'calendarWeeks' => 'true',
                                    'todayHighlight' => 'true',
                                    'orientation' => 'top left',
                                    'endDate' => '-1d',
                                ],
                                'options' => [
                                    'id' => "parentpersonaldata-{$keynum}-birthdate",
                                    $disabled => $disabled,
                                    'readonly' => $isReadonly,
                                    'autocomplete' => 'off'
                                ],
                                'maskOptions' => [
                                    'alias' => 'dd.mm.yyyy'
                                ]
                            ]
                        ); ?>
                    </div>

                    <div class="form-group <?= QuestionarySettings::getSettingByName('require_birth_place_parent') ? 'required' : '' ?>">
                        <?= $form->field($personal_data, 'birth_place', ['template' => "{label}{input}\n{error}"])
                            ->textInput(['readonly' => $isReadonly, $disabled => $disabled]); ?>
                    </div>

                    <div class="form-group">
                        <?= $form->field($personal_data, 'snils', ['template' => "{label}{input}\n{error}"])->widget(
                            MaskedInput::class,
                            [
                                'mask' => '999-999-999 99',
                                'clientOptions' => ['clearMaskOnLostFocus' => false],
                                'options' => [
                                    'id' => "parentpersonaldata-{$keynum}-snils",
                                    'class' => 'form-control',
                                    'readonly' => $isReadonly,
                                    $disabled => $disabled
                                ],
                            ]
                        ); ?>
                    </div>

                    <div class="form-group <?= QuestionarySettings::getSettingByName('require_ctitizenship_parent') ? 'required' : '' ?>">
                        <?php $citizenship_guid = Yii::$app->configurationManager->getCode('citizenship_guid');

                        $country = Country::findOne(['ref_key' => $citizenship_guid, 'archive' => false]);
                        $citizen_id = !empty($country) ? $country->id : null;

                        $citizenships = Country::find()->active()->orderBy('name')->all(); ?>
                        <?= $form->field($personal_data, 'country_id')->dropDownList(
                            ArrayHelper::map($citizenships, 'id', 'name'),
                            [
                                'prompt' => \Yii::t(
                                    'abiturient/questionary/block-basic-data',
                                    'Подпись пустого значения выпадающего списка для поля "country_id" формы в блоке "Основные данные" на странице анкеты поступающего: `Не указано`'
                                ),
                                'template' => "{label}{input}\n{error}",
                                'class' => 'form-control',
                                'readonly' => $isReadonly,
                                $disabled => $disabled
                            ]
                        ); ?>
                    </div>

                    <div class="form-group required">
                        <label class="col-form-label has-star">
                            <?= $personal_data->getAttributeLabel('main_phone') ?>
                        </label>

                        <?= $form->field($personal_data, 'main_phone', ['template' => $template])->widget(
                            MaskedInput::class,
                            [
                                'mask' => '+7(999)999-99-99',
                                'clientOptions' => ['prefix' => '+7', 'clearMaskOnLostFocus' => false],
                                'options' => [
                                    'id' => "parentpersonaldata-{$keynum}-main_phone",
                                    'class' => 'form-control',
                                    'readonly' => $isReadonly,
                                    $disabled => $disabled
                                ],
                            ]
                        ); ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="col-form-label">
                            <?= $personal_data->getAttributeLabel('secondary_phone') ?>
                        </label>

                        <?= $form->field($personal_data, 'secondary_phone', ['template' => $template])
                            ->textInput(['readonly' => $isReadonly, $disabled => $disabled]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>