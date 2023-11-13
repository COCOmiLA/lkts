<?php

namespace common\services\abiturientController\bachelor\bachelorSpeciality;

use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use common\components\configurationManager;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\MaxSpecialityType;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\SpecialityPriority;
use common\modules\abiturient\models\repositories\SpecialityRepository;
use common\services\abiturientController\bachelor\AdmissionAgreementService;
use common\services\abiturientController\BaseService;
use Exception;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\Request;
use yii\web\ServerErrorHttpException;

class SpecialityPrioritiesService extends BaseService
{
    const GROUP_MODE_ROW_NUMBER = 'НомерСтрокиЗаявления';
    const GROUP_MODE_COMPETITIVE_GROUP_STRUCTURE_DEP = 'СтруктурноеПодразделениеКонкурснойГруппы';
    const GROUP_MODE_COMPETITIVE_GROUP_EDU_FORM = 'ФормаОбученияКонкурснойГруппы';
    const GROUP_MODE_COMPETITIVE_GROUP_SPECIALITY = 'НаправлениеПодготовкиКонкурснойГруппы';
    const GROUP_MODE_COMPETITIVE_GROUP_UGS = 'УкрупненнаяГруппаСпециальностейКонкурснойГруппы';
    const GROUP_MODE_COMPETITIVE_GROUP_UGS_OR_SPECIALITY = 'НаправлениеПодготовкиУГСКонкурснойГруппы';
    const GROUP_MODE_COMPETITIVE_GROUP_ADMISSION_BASE = 'ОснованиеПоступленияКонкурснойГруппы';

    const GROUP_MODE_SEPARATE_STATEMENT_FOR_FULL_PAYMENT_BUDGET = 'SeparateSpecialitiesForBudgetAndFullPayment';

    protected AdmissionAgreementService $admissionAgreementService;

    




    public function __construct(
        Request                   $request,
        configurationManager      $configurationManager,
        AdmissionAgreementService $admissionAgreementService
    ) {
        parent::__construct($request, $configurationManager);
        $this->admissionAgreementService = $admissionAgreementService;
    }

    









    public function addSpecialitiesByIds(
        BachelorApplication $application,
        array               $addSpecialtyList,
        array               $userDefineSpecialityOrder = []
    ): array {

        if (!EmptyCheck::isEmpty($userDefineSpecialityOrder)) {
            $addSpecialtyList = $this->setUserDefineSpecialityOrder($addSpecialtyList, $userDefineSpecialityOrder);
        }

        $errors = [];
        $allowBenefitCategories = !ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
        $available_specialities = ArrayHelper::getColumn(
            SpecialityRepository::getCurrentAvailableSpecialities(
                $application,
                $allowBenefitCategories
            )->all(),
            'id'
        );

        $db = Speciality::getDb();
        $transaction = $db->beginTransaction();
        try {
            $all_added_spec = [];
            foreach ($addSpecialtyList as $speciality_id) {
                if (!in_array($speciality_id, $available_specialities)) {
                    continue;
                }
                $adding_spec = Speciality::findOne(['id' => (int)$speciality_id, 'archive' => false]);
                if (!$adding_spec) {
                    continue;
                }
                if ($application->hasSpeciality($adding_spec->id)) {
                    continue;
                }
                if ($this->maxSpecialityCountExceeded($application, $adding_spec)) {
                    $errors[] = Yii::t(
                        'abiturient/bachelor/application/all',
                        'Текст сообщения при добавлении НП, когда превышен лимит на их кол-во; на странице НП: `Не удалось добавить направление {specialityName}: превышено максимальное количество направлений`',
                        [
                            'specialityName' => $adding_spec->getFullName($application->type),
                        ]
                    );
                    continue;
                }
                if ($adding_spec->receipt_allowed && $adding_spec->isActiveByAdditionalReceiptDateControl()) {
                    $all_added_spec[] = $this->addSpeciality($application, $adding_spec);

                    if ($adding_spec->is_combined_competitive_group) {
                        
                        foreach ($adding_spec->childrenCombinedCompetitiveGroupRefSpecialities as $groupRefSpeciality) {
                            $all_added_spec[] = $this->addSpeciality($application, $groupRefSpeciality);
                        }
                    }

                    $application->addApplicationHistory(ApplicationHistory::TYPE_SPECIALITY_CHANGED);
                    $application->resetStatus();
                }
            }

            $this->admissionAgreementService->copyAgreementToAddedSpecialities($application, array_filter($all_added_spec));

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $errors;
    }

    public function validatePriorities(BachelorApplication $application): void
    {
        foreach ($application->specialities as $bachelorSpeciality) {
            $identifier = $this->getSpecialityPriorityIdentifier($application, $bachelorSpeciality);
            $priority = $bachelorSpeciality->specialityPriority;
            if ($priority && $priority->priority_group_identifier !== $identifier) {
                $priority->delete();
            }
        }
    }

    




    public function checkPrioritiesSettled(BachelorApplication $application): bool
    {
        $this->validatePriorities($application);

        return !$application->getSpecialities()
            ->andWhere(['speciality_priority.enrollment_priority' => null])
            ->exists();
    }

    public function setUpPriorities(BachelorApplication $application): void
    {
        
        $specialities = $application->getSpecialities()
            ->andWhere(['speciality_priority.enrollment_priority' => null])
            ->orderBy([BachelorSpeciality::tableName() . '.id' => SORT_DESC])
            ->all();
        foreach ($specialities as $speciality) {
            $this->setupPriorityForNewSpeciality($application, $speciality);
        }
    }

    









    public function removeSpeciality(BachelorApplication $application, BachelorSpeciality $speciality): void
    {
        $priority = $speciality->specialityPriority;
        $educationSourceRef = $speciality->speciality->educationSourceRef;
        if (!$speciality->archive()) {
            throw new UserException('Не удалось удалить направление');
        }
        if ($priority) {
            $this->settlePriorityAfterRemove($application, $educationSourceRef, $priority);
        }
        $application->addApplicationHistory(ApplicationHistory::TYPE_SPECIALITY_CHANGED);
    }

    public function changeSpecialityPriority(BachelorApplication $application, BachelorSpeciality $speciality, string $move_type)
    {
        if ($move_type == "up") {
            $this->upSpeciality($application, $speciality);
        } elseif ($move_type == "down") {
            $this->downSpeciality($application, $speciality);
        }

        $application->addApplicationHistory(ApplicationHistory::TYPE_SPECIALITY_CHANGED);
    }

    private function upSpeciality(BachelorApplication $application, BachelorSpeciality $speciality)
    {
        $specialityPriority = $speciality->specialityPriority;
        $identity = $specialityPriority->priority_group_identifier;
        $educationSourceRef = $speciality->speciality->educationSourceRef;
        $same_group_element_above = $this->getSpecialitiesWithSameGroup($application, $educationSourceRef, $identity)
            ->andWhere(['<', 'speciality_priority.inner_priority', $specialityPriority->inner_priority])
            ->orderBy(['speciality_priority.inner_priority' => SORT_DESC])
            ->limit(1)
            ->one();
        if ($same_group_element_above) {
            $same_group_element_above->specialityPriority->inner_priority = $specialityPriority->inner_priority;
            if (!$same_group_element_above->specialityPriority->save(true, ['inner_priority'])) {
                throw new RecordNotValid($same_group_element_above->specialityPriority);
            }
            $specialityPriority->inner_priority -= 1;
            if (!$specialityPriority->save(true, ['inner_priority'])) {
                throw new RecordNotValid($specialityPriority);
            }
        } else {
            $other_group_elements_above = $this->getSpecialitiesWithHigherGroup($application, $educationSourceRef, $specialityPriority)
                ->all();
            if ($other_group_elements_above) {
                $same_group_elements_above = $this->getSpecialitiesWithSameGroup($application, $educationSourceRef, $identity)->all();
                foreach ($other_group_elements_above as $other_group_element_above) {
                    $other_group_element_above->specialityPriority->enrollment_priority += 1;
                    if (!$other_group_element_above->specialityPriority->save(true, ['enrollment_priority'])) {
                        throw new RecordNotValid($other_group_element_above->specialityPriority);
                    }
                }
                foreach ($same_group_elements_above as $same_group_element_above) {
                    $same_group_element_above->specialityPriority->enrollment_priority -= 1;
                    if (!$same_group_element_above->specialityPriority->save(true, ['enrollment_priority'])) {
                        throw new RecordNotValid($same_group_element_above->specialityPriority);
                    }
                }
            } else {
                
                $specialityPriority->inner_priority = 1;
                $specialityPriority->enrollment_priority = 1;
                if (!$specialityPriority->save(true, ['inner_priority', 'enrollment_priority'])) {
                    throw new RecordNotValid($specialityPriority);
                }
            }
        }
    }

    private function downSpeciality(BachelorApplication $application, BachelorSpeciality $speciality)
    {
        $specialityPriority = $speciality->specialityPriority;
        $identity = $specialityPriority->priority_group_identifier;
        $educationSourceRef = $speciality->speciality->educationSourceRef;
        $same_group_element_below = $this->getSpecialitiesWithSameGroup($application, $educationSourceRef, $identity)
            ->andWhere(['>', 'speciality_priority.inner_priority', $specialityPriority->inner_priority])
            ->orderBy(['speciality_priority.inner_priority' => SORT_ASC])
            ->limit(1)
            ->one();
        if ($same_group_element_below) {
            $same_group_element_below->specialityPriority->inner_priority = $specialityPriority->inner_priority;
            if (!$same_group_element_below->specialityPriority->save(true, ['inner_priority'])) {
                throw new RecordNotValid($same_group_element_below->specialityPriority);
            }
            $specialityPriority->inner_priority += 1;
            if (!$specialityPriority->save(true, ['inner_priority'])) {
                throw new RecordNotValid($specialityPriority);
            }
        } else {
            $other_group_elements_below = $this->getSpecialitiesWithLowerGroup($application, $educationSourceRef, $specialityPriority)
                ->all();
            if ($other_group_elements_below) {
                $same_group_elements_below = $this->getSpecialitiesWithSameGroup($application, $educationSourceRef, $identity)->all();
                foreach ($other_group_elements_below as $other_group_element_below) {
                    $other_group_element_below->specialityPriority->enrollment_priority -= 1;
                    if (!$other_group_element_below->specialityPriority->save(true, ['enrollment_priority'])) {
                        throw new RecordNotValid($other_group_element_below->specialityPriority);
                    }
                }
                foreach ($same_group_elements_below as $same_group_element_below) {
                    $same_group_element_below->specialityPriority->enrollment_priority += 1;
                    if (!$same_group_element_below->specialityPriority->save(true, ['enrollment_priority'])) {
                        throw new RecordNotValid($same_group_element_below->specialityPriority);
                    }
                }
            }
        }
    }

    private function settlePriorityAfterRemove(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, SpecialityPriority $changed_priority)
    {
        
        $same_group = $this->getSpecialitiesWithSameGroup($application, $educationSourceRef, $changed_priority->priority_group_identifier)
            ->andWhere(['>', 'speciality_priority.inner_priority', $changed_priority->inner_priority])
            ->all();
        if ($same_group) {
            foreach ($same_group as $speciality) {
                $speciality->specialityPriority->inner_priority--;
                if (!$speciality->specialityPriority->save(true, ['inner_priority'])) {
                    throw new RecordNotValid($speciality->specialityPriority);
                }
            }
        } else {
            $lower_group = $this->getAllSpecialitiesWithLowerGroup($application, $educationSourceRef, $changed_priority)
                ->all();
            if ($lower_group) {
                foreach ($lower_group as $speciality) {
                    $speciality->specialityPriority->enrollment_priority--;
                    if (!$speciality->specialityPriority->save(true, ['enrollment_priority'])) {
                        throw new RecordNotValid($speciality->specialityPriority);
                    }
                }
            }
        }
    }

    private function setupPriorityForNewSpeciality(BachelorApplication $application, BachelorSpeciality $speciality): void
    {
        $priority_group_identifier = $this->getSpecialityPriorityIdentifier($application, $speciality);
        if (!$priority_group_identifier) {
            throw new ServerErrorHttpException('Не удалось определить приоритет для направления');
        }
        $educationSourceRef = $speciality->speciality->educationSourceRef;


        $lowest_speciality_with_same_group_query = $this
            ->getSpecialitiesWithSameGroup($application, $educationSourceRef, $priority_group_identifier);

        
        $lowest_speciality_with_same_group = $lowest_speciality_with_same_group_query
            ->orderBy(['speciality_priority.inner_priority' => SORT_DESC])
            ->limit(1)
            ->one();

        $priority = $speciality->specialityPriority;
        if (!$priority) {
            $priority = new SpecialityPriority();
            $priority->bachelor_speciality_id = $speciality->id;
        }
        $priority->priority_group_identifier = $priority_group_identifier;
        if ($lowest_speciality_with_same_group) {
            $priority->enrollment_priority = $lowest_speciality_with_same_group->specialityPriority->enrollment_priority;
            $priority->inner_priority = $lowest_speciality_with_same_group->specialityPriority->inner_priority + 1;
        } else {
            $priority->enrollment_priority = $this->maxEnrollmentPriority($application, $speciality) + 1;
            $priority->inner_priority = 1;
        }
        $priority->save(false);
    }

    





    private function addSpeciality(BachelorApplication $application, Speciality $adding_spec)
    {
        $onlyOneEducation = $application->onlyOneEducation;
        $basisAdmissionCategoryId = $application->basisAdmissionCategoryId;
        $onlyOneTargetReceptionId = $application->onlyOneTargetReceptionId;

        if ($application->hasSpeciality($adding_spec->id)) {
            return;
        }

        $bachelor_spec = new BachelorSpeciality();
        $bachelor_spec->speciality_id = $adding_spec->id;
        $bachelor_spec->application_id = $application->id;

        if ($basisAdmissionCategoryId && !$bachelor_spec->isCommercialBasis()) {
            $eduSourceReferenceUid = ArrayHelper::getValue($bachelor_spec, 'speciality.educationSourceRef.reference_uid');
            if ($eduSourceReferenceUid == BachelorSpeciality::getBudgetBasis()) {
                $bachelor_spec->admission_category_id = $basisAdmissionCategoryId;
                if (ArrayHelper::getValue($bachelor_spec, 'speciality.special_right')) {
                    $bachelor_spec->admission_category_id = $application->specificLawAdmissionCategoryId;
                }
            }
            if ($bachelor_spec->speciality && $bachelor_spec->speciality->isTargetReceipt()) {
                $bachelor_spec->target_reception_id = $onlyOneTargetReceptionId;
            }
        }

        if (!$bachelor_spec->save()) {
            throw new RecordNotValid($bachelor_spec);
        }
        if ($onlyOneEducation) {
            $bachelor_spec->link('educationsData', $onlyOneEducation);
        }
        if ($application->type->rawCampaign->common_education_document) {
            foreach ($application->educations as $education) {
                $bachelor_spec->link('educationsData', $education);
            }
        }

        $this->setupPriorityForNewSpeciality($application, $bachelor_spec);

        return $bachelor_spec;
    }

    




    private function getSpecialitiesWithSpecialityPriorityBaseQuery(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef): ActiveQuery
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $query = BachelorSpeciality::find()
            ->joinWith(['speciality', 'speciality.educationSourceRef'])
            ->innerJoinWith(['specialityPriority speciality_priority'])
            ->active()
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $application->id]);
        return $this->addDivisionForSpecialityByEducationSource($application, $educationSourceRef, $query);
    }

    private function addDivisionForSpecialityByEducationSource(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, ActiveQuery $query): ActiveQuery
    {
        $additional_query = [];
        $isSeparateStatementForFullPaymentBudget = $application->type->rawCampaign->separate_statement_for_full_payment_budget;
        if ($isSeparateStatementForFullPaymentBudget && $educationSourceRef) {
            $tnEducationSource = StoredEducationSourceReferenceType::tableName();

            if (in_array($educationSourceRef->reference_uid, [
                BachelorSpeciality::getBudgetBasis(),
                BachelorSpeciality::getTargetReceptionBasis(),
            ])) {
                $additional_query = ["{$tnEducationSource}.reference_uid" => [
                    BachelorSpeciality::getBudgetBasis(),
                    BachelorSpeciality::getTargetReceptionBasis(),
                ]];
            } else {
                $additional_query = ["{$tnEducationSource}.reference_uid" => BachelorSpeciality::getCommercialBasis(),];
            }
        }
        if ($additional_query) {
            $query->andWhere($additional_query);
        }
        return $query;
    }

    private function getSpecialitiesWithSameGroup(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, string $priority_group_identifier): ActiveQuery
    {
        return $this->getSpecialitiesWithSpecialityPriorityBaseQuery($application, $educationSourceRef)
            ->andWhere(['speciality_priority.priority_group_identifier' => $priority_group_identifier]);
    }

    private function getAllSpecialitiesWithLowerGroup(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, SpecialityPriority $priority): ActiveQuery
    {
        return $this->getSpecialitiesWithSpecialityPriorityBaseQuery($application, $educationSourceRef)
            ->andWhere(['>', 'speciality_priority.enrollment_priority', $priority->enrollment_priority]);
    }

    private function getSpecialitiesWithLowerGroup(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, SpecialityPriority $priority): ActiveQuery
    {
        return $this->getSpecialitiesWithSpecialityPriorityBaseQuery($application, $educationSourceRef)
            ->andWhere(['speciality_priority.enrollment_priority' => $priority->enrollment_priority + 1]);
    }

    private function getSpecialitiesWithHigherGroup(BachelorApplication $application, ?StoredEducationSourceReferenceType $educationSourceRef, SpecialityPriority $priority): ActiveQuery
    {
        return $this->getSpecialitiesWithSpecialityPriorityBaseQuery($application, $educationSourceRef)
            ->andWhere(['speciality_priority.enrollment_priority' => $priority->enrollment_priority - 1]);
    }

    public function maxInnerPriority(BachelorApplication $application, BachelorSpeciality $bachelorSpeciality): int
    {
        $priority = $bachelorSpeciality->specialityPriority;
        $priority_group_identifier = $priority->priority_group_identifier ?? $this->getSpecialityPriorityIdentifier($application, $bachelorSpeciality);

        $educationSourceRef = $bachelorSpeciality->speciality->educationSourceRef;

        $lowest_speciality_with_same_group_query = $this
            ->getSpecialitiesWithSameGroup($application, $educationSourceRef, $priority_group_identifier);

        $max_priority = $lowest_speciality_with_same_group_query
            ->select('speciality_priority.inner_priority')->max('speciality_priority.inner_priority');
        return $max_priority ?: 0;
    }

    public function maxEnrollmentPriority(BachelorApplication $application, BachelorSpeciality $bachelorSpeciality): int
    {
        $educationSourceRef = $bachelorSpeciality->speciality->educationSourceRef;

        $max_priority_query = $application
            ->getSpecialitiesWithoutOrdering()
            ->joinWith(['speciality', 'speciality.educationSourceRef'])
            ->innerJoinWith(['specialityPriority speciality_priority']);

        $max_priority_query = $this->addDivisionForSpecialityByEducationSource($application, $educationSourceRef, $max_priority_query);
        $max_priority = $max_priority_query
            ->select('speciality_priority.enrollment_priority')->max('speciality_priority.enrollment_priority');
        return $max_priority ?: 0;
    }

    public function getSpecialityPriorityIdentifier(BachelorApplication $application, BachelorSpeciality $bachelor_speciality): string
    {
        $speciality = $bachelor_speciality->speciality;

        $group_modes = ArrayHelper::getValue($application, 'type.campaign.specialityGroupingModes', []);
        $group_modes = array_column($group_modes, 'code_name');
        if (!$group_modes) {
            $group_modes = [SpecialityPrioritiesService::GROUP_MODE_ROW_NUMBER];
        }
        if ($application->type->rawCampaign->separate_statement_for_full_payment_budget) {
            $group_modes[] = SpecialityPrioritiesService::GROUP_MODE_SEPARATE_STATEMENT_FOR_FULL_PAYMENT_BUDGET;
        }
        return implode('_', array_map(function (string $mode_name) use ($bachelor_speciality, $speciality) {
            switch ($mode_name) {
                case SpecialityPrioritiesService::GROUP_MODE_ROW_NUMBER:
                    return $bachelor_speciality->id;

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_STRUCTURE_DEP:
                    return ArrayHelper::getValue($speciality, 'branchRef.reference_uid');

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_EDU_FORM:
                    return ArrayHelper::getValue($speciality, 'educationFormRef.reference_uid');

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_SPECIALITY:
                    return ArrayHelper::getValue($speciality, 'directionRef.reference_uid');

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_UGS:
                    return ArrayHelper::getValue($speciality, 'ugsRef.reference_uid');

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_UGS_OR_SPECIALITY:
                    $main_info = ArrayHelper::getValue(
                        $speciality,
                        'ugsRef.reference_uid',
                        ArrayHelper::getValue($speciality, 'directionRef.reference_uid', '')
                    );
                    return $main_info . ArrayHelper::getValue($speciality, 'profileRef.reference_uid', '');

                case SpecialityPrioritiesService::GROUP_MODE_COMPETITIVE_GROUP_ADMISSION_BASE:
                    return ArrayHelper::getValue($speciality, 'educationSourceRef.reference_uid', '');

                case SpecialityPrioritiesService::GROUP_MODE_SEPARATE_STATEMENT_FOR_FULL_PAYMENT_BUDGET:
                    $educationSourceReferenceUid = ArrayHelper::getValue($speciality, 'educationSourceRef.reference_uid');
                    if ($educationSourceReferenceUid == BachelorSpeciality::getCommercialBasis()) {
                        return 'CommercialBasis';
                    }

                    return 'BadgedBasis';

                default:
                    throw new ServerErrorHttpException('Неизвестный режим группировки');
            }
        }, $group_modes));
    }

    







    protected function maxSpecialityCountExceeded(BachelorApplication $application, Speciality $specialityToAdd): bool
    {
        $campaign = ArrayHelper::getValue($application, 'type.campaign');

        
        if (!$campaign->max_speciality_count) {
            return false;
        }

        
        if (!$campaign->getMaxSpecialityType()) {
            return false;
        }

        $uniqueSpecsCount = $this->getUniqueSpecialityCount($application, $specialityToAdd);

        return ($uniqueSpecsCount > (int)$campaign->max_speciality_count);
    }

    protected function getUniqueSpecialityCount(BachelorApplication $application, Speciality $specialityToAdd = null): int
    {
        $campaign = ArrayHelper::getValue($application, 'type.campaign');

        $bachelorSpecialitiesQuery = $application->getSpecialities()->with(['speciality']);

        if ($campaign->getMaxSpecialityType() == MaxSpecialityType::TYPE_SPECIALITY) {
            $bachelorSpecialitiesQuery->with[] = 'speciality.directionRef';
            $getCodes = function (Speciality $spec, array $used_ugs_refs, array $used_competitive_group_refs) {
                return ArrayHelper::getValue($spec, 'directionRef.reference_uid');
            };
        } elseif ($campaign->getMaxSpecialityType() == MaxSpecialityType::TYPE_GROUP) {
            $bachelorSpecialitiesQuery->with[] = 'speciality.competitiveGroupRef';
            $getCodes = function (Speciality $spec, array $used_ugs_refs, array $used_competitive_group_refs) {
                return ArrayHelper::getValue($spec, 'competitiveGroupRef.reference_uid');
            };
        } elseif ($campaign->getMaxSpecialityType() == MaxSpecialityType::TYPE_FACULTY) {
            $bachelorSpecialitiesQuery->with[] = 'speciality.subdivisionRef';
            $getCodes = function (Speciality $spec, array $used_ugs_refs, array $used_competitive_group_refs) {
                return ArrayHelper::getValue($spec, 'subdivisionRef.reference_uid');
            };
        } elseif ($campaign->getMaxSpecialityType() == MaxSpecialityType::TYPE_UGS) {
            $bachelorSpecialitiesQuery->with[] = 'speciality.directionRef';
            $bachelorSpecialitiesQuery->with[] = 'speciality.ugsRef';
            $bachelorSpecialitiesQuery->with[] = 'speciality.educationSourceRef';
            $bachelorSpecialitiesQuery->with[] = 'speciality.competitiveGroupRef';
            $count_targets_by_discipline = $campaign->count_target_specs_separately;
            $getCodes = function (Speciality $spec, array $used_ugs_refs, array $used_competitive_group_refs) use ($count_targets_by_discipline) {
                $uid = ArrayHelper::getValue($spec, 'directionRef.reference_class_name') . ArrayHelper::getValue($spec, 'directionRef.reference_uid');
                $ugsRef = ArrayHelper::getValue($spec, 'ugsRef');
                if (!$ugsRef) {
                    
                    $spec_with_same_direction_in_other_cg = Speciality::find()
                        ->joinWith(['directionRef direction_ref', 'ugsRef ugs_ref', 'competitiveGroupRef competitive_group_ref'])
                        ->andWhere(['direction_ref.reference_uid' => ArrayHelper::getValue($spec, 'directionRef.reference_uid')])
                        ->andWhere(['competitive_group_ref.reference_uid' => ArrayHelper::getColumn($used_competitive_group_refs, 'reference_uid')])
                        ->andWhere(['ugs_ref.reference_uid' => ArrayHelper::getColumn($used_ugs_refs, 'reference_uid')])
                        ->one();
                    if ($spec_with_same_direction_in_other_cg) {
                        $ugsRef = $spec_with_same_direction_in_other_cg->ugsRef;
                    }
                }
                if ($ugsRef) {
                    $is_target = $spec->isTargetReceipt();
                    if (!$is_target || !$count_targets_by_discipline) {
                        $uid = ArrayHelper::getValue($ugsRef, 'reference_class_name') . ArrayHelper::getValue($ugsRef, 'reference_uid');
                    }
                }
                return $uid;
            };
        } else {
            $bachelorSpecialitiesQuery->with[] = 'speciality.directionRef';
            $getCodes = function (Speciality $spec, array $used_ugs_refs, array $used_competitive_group_refs) {
                return ArrayHelper::getValue($spec, 'directionRef.reference_uid');
            };
        }
        
        $bachelorSpecialities = $bachelorSpecialitiesQuery->all();

        $specialities = ArrayHelper::getColumn($bachelorSpecialities, 'speciality');
        $specialityIds = ArrayHelper::getColumn($specialities, 'id');
        if ($specialityToAdd !== null) {
            $specialities[] = $specialityToAdd;
        }

        $codes = [];
        $used_ugs_refs = array_values(array_unique(ArrayHelper::getColumn($specialities, 'ugsRef')));
        $used_competitive_group_refs = array_values(array_unique(ArrayHelper::getColumn($specialities, 'competitiveGroupRef')));
        foreach ($specialities as $spec) {
            $speciality = $spec;
            if ($spec->parentCombinedCompetitiveGroupRef) {
                
                $speciality = $spec->getParentCombinedCompetitiveGroupRefSpeciality()
                    ->andWhere(['dictionary_speciality.id' => $specialityIds])
                    ->one() ?? $spec;
            }
            $codes[] = $getCodes($speciality, $used_ugs_refs, $used_competitive_group_refs);
        }
        return count(array_unique($codes));
    }

    





    protected function setUserDefineSpecialityOrder(array $oldList, array $order): array
    {
        $newList = [];
        foreach ($order as $specialityId) {
            if (!key_exists($specialityId, $oldList)) {
                continue;
            }

            $newList[] = $specialityId;
            unset($oldList[$specialityId]);
        }

        if ($oldList) {
            $newList = array_merge($newList, $oldList);
        }

        return $newList;
    }

    public function getFinancialBasisFilterForBudget(): array
    {
        return [
            BachelorSpeciality::getBudgetBasis(),
            BachelorSpeciality::getTargetReceptionBasis(),
        ];
    }

    public function getFinancialBasisFilterForCommercial(): array
    {
        return [BachelorSpeciality::getCommercialBasis()];
    }

    public function getFinanceArrayForBudget(array $finance_array): array
    {
        return $this->filterFinanceArray($finance_array, $this->getFinancialBasisFilterForBudget());
    }

    public function getFinanceArrayForCommercial(array $finance_array): array
    {
        return $this->filterFinanceArray($finance_array, $this->getFinancialBasisFilterForCommercial());
    }

    private function filterFinanceArray(array $finance_array, array $allowed_keys): array
    {
        return array_filter(
            $finance_array,
            function ($key) use ($allowed_keys) {
                return in_array($key, $allowed_keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
