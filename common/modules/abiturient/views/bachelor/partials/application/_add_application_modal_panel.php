<?php

use common\models\dictionary\Speciality;
use common\models\EmptyCheck;
use yii\helpers\ArrayHelper;
use yii\web\View;











$displayStyle = '';
if ($availableSpecialty->detailGroupRef && $availableSpecialty->detailGroupRef->reference_id == '1') {
    $displayStyle = 'style="display:none;"';
}

$dataCode = str_replace('.', '', $availableSpecialty->speciality_human_code);
$dataTitle = $availableSpecialty->speciality_human_code .
    ' ' .
    mb_strtolower($availableSpecialty->directionRef->reference_name ?? '', 'UTF-8') .
    ' ' .
    mb_strtolower($availableSpecialty->profileRef->reference_name ?? '');

?>

<div 
    class="card mb-2 <?= $availableSpecialty->is_combined_competitive_group ? 'combined-group' : '' ?>" <?= $displayStyle; ?>
    id="<?= "add_application_modal_panel--{$modelIdentification}"; ?>"
    data-specid="<?= $availableSpecialty->id; ?>"
    data-dep="<?= $availableSpecialty->subdivisionRef->reference_uid; ?>"
    data-eduf="<?= $availableSpecialty->educationFormRef->reference_uid; ?>"
    data-fin="<?= $availableSpecialty->educationSourceRef->reference_uid; ?>"
    data-title="<?= $dataTitle; ?>"
    data-group="<?= $availableSpecialty->competitiveGroupRef->reference_uid; ?>"
    data-special_right="<?= $availableSpecialty->special_right ? 1 : 0; ?>"
    data-detail_group="<?= ArrayHelper::getValue($availableSpecialty, 'detailGroupRef.reference_uid', ''); ?>"
    data-code="<?= $dataCode; ?>"
>
    <?php $isActiveByAdditionalReceiptDateControl = $availableSpecialty->isActiveByAdditionalReceiptDateControl(); ?>
    <div class="card-header toggleable-panel" data-specid="<?= $availableSpecialty->id; ?>">
        <div class="row">
            <div class="col-10 col-lg-11">
                <div class="add-spec-header">
                    <strong>
                        <?php if (!isset($displayCode) || $displayCode == 1) {
                            echo $availableSpecialty->speciality_human_code . ' ';
                        }
                        if (!isset($displaySpecialityName) && !isset($displayGroupName)) {
                            echo $availableSpecialty->directionRef->reference_name ?? '';
                        } else {
                            $result_name = ($displaySpecialityName == 1 ? $availableSpecialty->directionRef->reference_name ?? '' : '') . ' ' . ($displayGroupName == 1 ? $availableSpecialty->competitiveGroupRef->reference_name ?? '' : '');
                            echo trim((string)$result_name);
                        } ?>
                    </strong>
                    <?php if (!EmptyCheck::isEmpty($availableSpecialty->profileRef)) {
                        echo " ({$availableSpecialty->profileRef->reference_name}), ";
                    } ?>
                    <?php if (!EmptyCheck::isEmpty($availableSpecialty->graduatingDepartmentName)) {
                        echo " {$availableSpecialty->graduatingDepartmentName}, ";
                    } ?>
                    <?php echo mb_strtolower($availableSpecialty->educationLevelRef->reference_name ?? '', 'UTF-8'); ?>,
                    <?= mb_strtolower($availableSpecialty->educationSourceRef->reference_name ?? '', 'UTF-8'); ?>,
                    <?php $formStudy = mb_strtolower($availableSpecialty->educationFormRef->reference_name ?? '', 'UTF-8') . ' ';
                    $formStudy .= Yii::t(
                        'abiturient/bachelor/application/application-modal',
                        'Окончание фразы для текста "форма обучения" в плашке направления; модального окна добавления НП на странице НП: `форма обучения`'
                    );
                    if ($availableSpecialty->detailGroupRef) {
                        $formStudy .= ', ' . mb_strtolower($availableSpecialty->detailGroupRef->reference_name, 'UTF-8');
                    }
                    if ($availableSpecialty->special_right) {
                        $formStudy .= ', ' . Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Текст информирующий о наличии особого права в плашке направления; модального окна добавления НП на странице НП: `особое право`'
                            );
                    } ?>
                    <?= $formStudy ?>
                </div>
            </div>

            <div class="col-2 col-lg-1">
                <?php if ($isActiveByAdditionalReceiptDateControl) : ?>
                    <input
                        class="speciality-select"
                        name="spec[<?= $availableSpecialty->id; ?>]"
                        type="checkbox"
                        value="<?= $availableSpecialty->id; ?>"
                    >
                <?php endif; ?>

                <i
                    class="fa fa-caret-down toggle-panel float-right"
                    aria-hidden="true"
                    data-specid="<?= $availableSpecialty->id; ?>"
                ></i>
            </div>
        </div>
    </div>

    <div class="card-body hide-body" id="body-<?= $availableSpecialty->id; ?>">
        <?php if (!$isActiveByAdditionalReceiptDateControl) : ?>
            <div class="alert alert-warning">
                <?= Yii::t(
                    'abiturient/bachelor/application/application-modal',
                    'Текст, поясняющий почему нельзя добавить НП; модального окна добавления НП на странице НП: `Прием документов по направлению подготовки в данный момент невозможен. Доступные даты: {formattedAdditionalReceiptDates}.`',
                    ['formattedAdditionalReceiptDates' => $availableSpecialty->getFormattedAdditionalReceiptDates()]
                ) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <?= $availableSpecialty->educationLevelRef->reference_name ?? ''; ?>

                <span class="float-right">
                    <?= $availableSpecialty->getAttributeLabel('finance_name') ?>: <?= $availableSpecialty->educationSourceRef->reference_name ?? ''; ?>
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= $availableSpecialty->getAttributeLabel('eduform_name') ?>
                : <?= $availableSpecialty->educationFormRef->reference_name ?? ''; ?>

                <?php if ($availableSpecialty->detailGroupRef) : ?>
                    <span class="float-right">
                        <?= $availableSpecialty->getAttributeLabel('detail_group_name') ?>: <?= $availableSpecialty->detailGroupRef->reference_name ?? ''; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php
                $faculty_name = $availableSpecialty->subdivisionRef->reference_name ?? '';
                $department_name = $availableSpecialty->graduatingDepartmentName;
                if ($faculty_name) {
                    if ($department_name) {
                        echo $faculty_name . ' (' . $department_name . ')';
                    } else {
                        echo $faculty_name;
                    }
                } else {
                    echo $department_name;
                }
                ?>

                <span class="float-right">
                    <?= $availableSpecialty->getAttributeLabel('group_name') ?>: <?= $availableSpecialty->competitiveGroupRef->reference_name ?? ''; ?>
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= $availableSpecialty->getAttributeLabel('curriculum_ref_id') ?>: <?= $availableSpecialty->curriculumRef->reference_name ?? ''; ?>
            </div>
        </div>
    </div>
</div>