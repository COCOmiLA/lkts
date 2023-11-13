<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\EgeResult;
use yii\helpers\ArrayHelper;
use yii\web\View;








$hasCorrectCitizenship = BachelorResultCentralizedTesting::hasCorrectCitizenship($application);
foreach ($egeResults as $result) :
    
    $index = $result->id;
    $result->_application = $application;
    $isExam = $result->isExam();
    $divider = '3';
    if ($isExam) {
        $divider = '2';
    }
    if (empty($result->egeyear)) {
        $result->egeyear = (string)date('Y');
    } ?>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-11">
                    <div class="row">
                        <?php $referenceName = ArrayHelper::getValue($result, 'cgetDiscipline.reference_name') ?? '-' ?>
                        <?php if ($result->hasChildren()) : ?>
                            <div class="col-md-<?= $divider ?>">
                                <?= "{$result->getAttributeLabel('cget_discipline_id')} \"{$referenceName}\"" ?>

                                <br />

                                <div class="help_block_leveler">
                                    "<?= ArrayHelper::getValue($result, 'cgetChildDiscipline.reference_name') ?? '-' ?>"
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="col-md-<?= $divider ?>">
                                <?= $result->getAttributeLabel('cget_discipline_id') ?>

                                <br />

                                <div class="help_block_leveler">
                                    "<?= $referenceName ?>"
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-2">
                            <?= $result->getAttributeLabel('cget_exam_form_id') ?>

                            <br />

                            <div class="help_block_leveler">
                                "<?= ArrayHelper::getValue($result, 'cgetExamForm.reference_name') ?? '-' ?>"
                            </div>
                        </div>

                        <?php if ($isExam) : ?>
                            <div class="col-md-2">
                                <?= $result->getAttributeLabel('reason_for_exam_id') ?>

                                <br />

                                <div class="help_block_leveler">
                                    "<?= ArrayHelper::getValue($result, 'reasonForExam.name') ?? '-' ?>"
                                </div>
                            </div>

                            <?php if ($application->type->allow_language_selection) : ?>
                                <div class="col-md-2">
                                    <?= $result->getAttributeLabel('language_id') ?>

                                    <br />

                                    <div class="help_block_leveler">
                                        "<?= ArrayHelper::getValue($result, 'language.description') ?? '-' ?>"
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($application->type->allow_special_requirement_selection) : ?>
                                <div class="col-md-3">
                                    <?= $result->getAttributeLabel('special_requirement_ref_id') ?>

                                    <br />

                                    <div class="help_block_leveler">
                                        "<?= ArrayHelper::getValue($result, 'specialRequirement.reference_name') ?? '-' ?>
                                        "
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else : ?>
                            <div class="col-md-3">
                                <?= $result->getAttributeLabel('egeyear') ?>

                                <br />

                                <div class="help_block_leveler">
                                    "<?= ArrayHelper::getValue($result, 'egeyear') ?? '-' ?>"
                                </div>
                            </div>

                            <div class="col-md-3">
                                <?= $result->getAttributeLabel('discipline_points') ?>

                                <br />

                                <div class="help_block_leveler">
                                    "<?= ArrayHelper::getValue($result, 'discipline_points') ?? '-' ?>"
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                    <?php if (
                        $isExam &&
                        $hasCorrectCitizenship
                    ) : ?>
                        <?php 
                        $centralizedTesting = $result->getOrBuildCentralizedTesting(); ?>
                        <?php if (!$centralizedTesting->isNew) : ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h4>
                                                <?= $centralizedTesting->labelForCollapse ?>
                                            </h4>
                                        </div>

                                        <div class="card-body">
                                            <?= $this->render(
                                                '_staticCentralizedTesting',
                                                ['centralizedTesting' => $centralizedTesting]
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-1">
                    <?php if ($result->status == EgeResult::STATUS_VERIFIED) : ?>
                        <div style="text-align: center;">
                            <i class="fa fa-check verified_status super_centric"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach;