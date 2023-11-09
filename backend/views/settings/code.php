<?php

use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\MaxSpecialityType;
use kartik\form\ActiveFormAsset;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

ActiveFormAsset::register($this);

$appLanguage = Yii::$app->language;

$this->title = 'Настройки кодов справочников';
$exceptions = array("scan_sort_code");
$disabledCodes = CodeSettingsManager::GetDisabledCodes();

?>

<?php $form = ActiveForm::begin([
    'id' => 'text-form',
    'options' => ['name' => 'TextForm'],
    'fieldConfig' => [
        'template' => "{input}\n{error}"
    ]
]); ?>

    <table class="table text-table" id="table-id">
        <thead>
        <tr>
            <th>Описание</th>
            <th>Код</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        <?php foreach ($codes as $code) : ?>
            <?php if (!in_array($code->name, $disabledCodes)) : ?>
                <tr id="<?php echo $code->name ?>">
                    <?php if (in_array($code->name, $exceptions)) : ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php $isRequired = CodeSettingsManager::IsRequired($code); ?>
                    <td style="width: 25%;" class="<?php echo $isRequired ? 'required' : '' ?>">
                        <sapn class="<?php echo $isRequired ? 'has-star' : '' ?>"><?= $code->description; ?></span>
                        <?= Html::hiddenInput('CodeSetting[' . $i . '][id]', $code->id); ?>
                    </td>

                    <td style="width: 65%;">
                        <?php
                        if (in_array($code->name, [
                            'can_change_fio_after_first_application',
                            'confirm-email',
                            'required_abiturient_avatar',
                            'allow_dump_full_package_to_file',
                            'allow_return_approved_application_to_sent',
                        ])) {
                            $data = ['1' => 'Да', '0' => 'Нет'];
                        } elseif ($code->name == 'without_entrant_tests_variant') {
                            $data = ArrayHelper::map(StoredVariantOfRetestReferenceType::find()->notMarkedToDelete()->active()->andWhere(['is_folder' => false])->all(), 'id', 'reference_name');
                        } elseif (CodeSettingsManager::CheckCodeInArrayCodesToEntity($code->name, false)) {
                            $data = CodeSettingsManager::GetMappedCodeEntityArray($code->name);
                        }

                        if ($code->name == "min_age") {
                            echo Html::activeInput('number', $code, 'value', [
                                'class' => 'form-control',
                                'name' => 'CodeSetting[' . $i . '][value]',
                                'id' => 'codesetting-value' . $i,
                            ]);
                        } elseif ($code->name == "display_not_found_in_classifier") {
                            echo Html::activeCheckbox($code, 'value', [
                                'name' => 'CodeSetting[' . $i . '][value]',
                                'id' => 'codesetting-value' . $i,
                                'label' => ''
                            ]);
                        } elseif ($code->name == 'part_time_education_form_code') {
                            echo Html::activeInput('text', $code, 'value', [
                                'class' => 'form-control',
                                'name' => 'CodeSetting[' . $i . '][value]',
                                'id' => 'codesetting-value' . $i,
                            ]);
                        } else {
                            echo Select2::widget([
                                'language' => $appLanguage,
                                'model' => $code,
                                'name' => 'CodeSetting[' . $i . '][value]',
                                'attribute' => 'value',
                                'data' => $data,
                                'options' => [
                                    'placeholder' => 'Выберите ...',
                                    'name' => 'CodeSetting[' . $i . '][value]',
                                    'id' => 'codesetting-value' . $i,
                                ],
                                'pluginOptions' => [
                                    'multiple' => false,
                                    'allowClear' => !$isRequired,
                                    'dropdownParent' => '#table-id',
                                ],
                            ]);
                        }
                        ?>
                    </td>
                </tr>
                <?php $i++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
<?php ActiveForm::end();
