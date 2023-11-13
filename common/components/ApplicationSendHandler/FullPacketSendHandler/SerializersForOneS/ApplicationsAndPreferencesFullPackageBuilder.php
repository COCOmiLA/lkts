<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders\BenefitPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders\OlympicPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders\TargetPackageBuilder;
use common\components\BooleanCaster;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\UUIDManager;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredBudgetLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\bachelor\SpecialityPriority;
use common\services\abiturientController\bachelor\bachelorSpeciality\SpecialityPrioritiesService;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ApplicationsAndPreferencesFullPackageBuilder extends BaseApplicationPackageBuilder
{
    public $raw_benefits;
    public $raw_targets;
    public $raw_olympics;

    public $raw_educations;

    protected SpecialityPrioritiesService $specialityPrioritiesService;

    public function __construct(?BachelorApplication $app, SpecialityPrioritiesService $specialityPrioritiesService)
    {
        parent::__construct($app);
        $this->specialityPrioritiesService = $specialityPrioritiesService;
    }

    public function setRawBenefits($raw_benefits, $raw_olympics, $raw_targets)
    {
        if (
            !EmptyCheck::isEmpty($raw_benefits) &&
            (!is_array($raw_benefits) || ArrayHelper::isAssociative($raw_benefits))
        ) {
            $raw_benefits = [$raw_benefits];
        }
        $this->raw_benefits = $raw_benefits;

        if (
            !EmptyCheck::isEmpty($raw_olympics) &&
            (!is_array($raw_olympics) || ArrayHelper::isAssociative($raw_olympics))
        ) {
            $raw_olympics = [$raw_olympics];
        }
        $this->raw_olympics = $raw_olympics;

        if (
            !EmptyCheck::isEmpty($raw_targets) &&
            (!is_array($raw_targets) || ArrayHelper::isAssociative($raw_targets))
        ) {
            $raw_targets = [$raw_targets];
        }
        $this->raw_targets = $raw_targets;

        return $this;
    }

    public function setRawEducations($raw_educations)
    {
        $this->raw_educations = $raw_educations;
        return $this;
    }

    public function build()
    {
        $attached_benefits = [
            'Benefits' => [],
            'Targets' => [],
            'Olympics' => [],
        ];

        $specialities = [];
        $bach_specs = $this->getSpecialities()
            ->with([
                'preference.documentType',
                'preference.privilege',
                'preference.specialMark',
                'targetReception.documentType',
                'speciality.competitiveGroupRef',
                'speciality.profileRef',
                'speciality.educationSourceRef',
                'speciality.budgetLevelRef',
                'admissionCategory',
            ])
            ->all();
        [$all_educations, $built_educations] = (new EducationFullPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->build();
        foreach ($bach_specs as $number => $spec) {
            $tmp_spec = $this->buildSpeciality($number + 1, $spec, $attached_benefits, $all_educations);
            $specialities[] = $tmp_spec;
        }
        
        $attached_benefits['Benefits'] = $this->uniqueById($attached_benefits['Benefits']);
        $attached_benefits['Targets'] = $this->uniqueById($attached_benefits['Targets']);
        $attached_benefits['Olympics'] = $this->uniqueById($attached_benefits['Olympics']);

        
        $olymps = $this->application
            ->getRawBachelorPreferencesOlymp()
            ->onlyRecentlyRemovedAndActualRecords($this->application->approved_at)
            ->sortByArchiveFlag()
            ->all();
        foreach ($olymps as $olymp) {
            if (!$this->suchRecordAlreadyExists($attached_benefits['Olympics'], $olymp)) {
                $olymp->tmp_uuid = UUIDManager::GetUUID();
                $attached_benefits['Olympics'][] = $olymp;
            }
        }

        $preferencesSpecialRights = $this->application
            ->getRawBachelorPreferencesSpecialRight()
            ->onlyRecentlyRemovedAndActualRecords($this->application->approved_at)
            ->sortByArchiveFlag()
            ->all();
        foreach ($preferencesSpecialRights as $specialRight) {
            if (!$this->suchRecordAlreadyExists($attached_benefits['Benefits'], $specialRight)) {
                $specialRight->tmp_uuid = UUIDManager::GetUUID();
                $attached_benefits['Benefits'][] = $specialRight;
            }
        }

        $targetReceptions = $this->application
            ->getRawTargetReceptions()
            ->onlyRecentlyRemovedAndActualRecords($this->application->approved_at)
            ->sortByArchiveFlag()
            ->all();
        foreach ($targetReceptions as $targetReception) {
            if (!$this->suchRecordAlreadyExists($attached_benefits['Targets'], $targetReception)) {
                $targetReception->tmp_uuid = UUIDManager::GetUUID();
                $attached_benefits['Targets'][] = $targetReception;
            }
        }
        return [
            'Olympics' => (new OlympicPackageBuilder($this->application))
                ->setPrefenences($attached_benefits['Olympics'])
                ->setFilesSyncer($this->files_syncer)
                ->build(),
            'Benefits' => (new BenefitPackageBuilder($this->application))
                ->setPrefenences($attached_benefits['Benefits'])
                ->setFilesSyncer($this->files_syncer)
                ->build(),
            'Targets' => (new TargetPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setTargets($attached_benefits['Targets'])
                ->build(),
            'Applications' => $specialities,
            'EducationDocuments' => $built_educations,
        ];
    }

    


    private function getSpecialities(): ActiveQuery
    {
        $baseSpecialitiesQuery = $this->application->getSpecialities();
        if (!($callback = $this->getSpecialitiesFiltrationCallback())) {
            return $baseSpecialitiesQuery;
        }

        return $callback($baseSpecialitiesQuery);
    }

    







    private function buildSpeciality(
        int                $rowNumber,
        BachelorSpeciality $spec,
        array             &$attached,
        array              $all_educations
    ) {
        $educationsDataId = array_map(
            function ($educationsData) {
                return ArrayHelper::getValue($educationsData, 'id');
            },
            $spec->educationsData
        );

        $preference = $spec->preference;
        $targetReception = $spec->targetReception;
        $olympiad = $spec->bachelorOlympiad;
        $BenefitGUID = '';
        $TargetGUID = '';
        $OlympicGUID = '';
        if ($targetReception) {
            $targetReception->tmp_uuid = $this->getUUIDByRecord($attached['Targets'], $targetReception);
            $TargetGUID = $targetReception->tmp_uuid;
            $attached['Targets'][] = $targetReception;
        }
        if ($olympiad) {
            $olympiad->tmp_uuid = $this->getUUIDByRecord($attached['Olympics'], $olympiad);
            $OlympicGUID = $olympiad->tmp_uuid;
            $attached['Olympics'][] = $olympiad;
        }
        if ($preference) {
            $preference->tmp_uuid = $this->getUUIDByRecord($attached['Benefits'], $preference);
            $BenefitGUID = $preference->tmp_uuid;
            $attached['Benefits'][] = $preference;
        }
        $AdmissionCategoryRef = ReferenceTypeManager::GetReference($spec, 'admissionCategory');

        $EducationDocuments = array_map(
            function ($filteredEducations) {
                return ArrayHelper::getValue($filteredEducations, 'tmp_uuid');
            },
            array_values(array_filter(
                $all_educations,
                function ($edu) use ($educationsDataId) {
                    return in_array($edu->id, $educationsDataId);
                }
            ))
        );
        $result = [
            'RowNumber' => $rowNumber,
            'EnrollmentPriority' => $spec->specialityPriority->enrollment_priority,
            'CampaignRef' => ReferenceTypeManager::GetReference($this->application->type->campaign, 'referenceType'),
            'CompetitiveGroupRef' => ReferenceTypeManager::GetReference($spec->speciality, 'competitiveGroupRef'),
            'CurriculumRef' => ReferenceTypeManager::GetReference($spec->speciality, 'curriculumRef'),
            'DirectionOKSO' => $spec->speciality->speciality_human_code ?? '',
            'ProfileRef' => ReferenceTypeManager::GetReference($spec->speciality, 'profileRef'),
            'EducationSourceRef' => ReferenceTypeManager::GetReference($spec->speciality, 'educationSourceRef'),
            'AdmissionCategoryRef' => $AdmissionCategoryRef,
            'LevelBudgetRef' => ReferenceTypeManager::GetReference($spec->speciality, 'budgetLevelRef'),
            'Agreed' => BooleanCaster::toInt(!empty($spec->agreement)), 
            'EducationDocuments' => $EducationDocuments,
            'BenefitTempGUID' => $BenefitGUID,
            'TargetTempGUID' => $TargetGUID,
            'OlympicTempGUID' => $OlympicGUID,
            'ApplicationStringGUID' => $spec->application_code,
        ];
        $entranceTests = (new EntranceTestSetsFullPackageBuilder($this->application, $spec))->build();
        if ($entranceTests) {
            $result['EntranceTests'] = $entranceTests;
        }
        $files = (new ScansFullPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->setFileLinkableEntity($spec)
            ->build();
        if ($files) {
            $result['Files'] = $files;
        }
        return $result;
    }

    protected function getUUIDByRecord(array $haystack, ActiveRecord $record): string
    {
        foreach ($haystack as $attached_instance) {
            if ($attached_instance->id == $record->id) {
                return $attached_instance->tmp_uuid;
            }
        }
        return UUIDManager::GetUUID();
    }

    protected function uniqueById(array $toUniq): array
    {
        $uniqed = [];
        foreach ($toUniq as $record) {
            if (!$this->suchRecordAlreadyExists($uniqed, $record)) {
                $uniqed[] = $record;
            }
        }
        return $uniqed;
    }

    protected function suchRecordAlreadyExists(array $haystack, ActiveRecord $needle): bool
    {
        return !empty(array_values(array_filter($haystack, function ($item) use ($needle) {
            return $item->id == $needle->id;
        })));
    }

    protected function getSpecialityGroupByEducationSource(BachelorSpeciality $local_bach_spec): string
    {
        $isSeparateStatementForFullPaymentBudget = $this->application->type->rawCampaign->separate_statement_for_full_payment_budget;
        if (!$isSeparateStatementForFullPaymentBudget) {
            return 'all';
        }

        $educationSourceRef = $local_bach_spec->speciality->educationSourceRef;
        if (in_array($educationSourceRef->reference_uid, [
            BachelorSpeciality::getBudgetBasis(),
            BachelorSpeciality::getTargetReceptionBasis(),
        ])) {
            return 'budget';
        } else {
            return 'commercial';
        }
    }

    public function update($raw_data): bool
    {
        $this->updateBenefits();
        $all_educations = (new EducationFullPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->update($this->raw_educations);

        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            $raw_data = [];
        }
        if (!is_array($raw_data) || ArrayHelper::isAssociative($raw_data)) {
            $raw_data = [$raw_data];
        }
        $touched_speciality_ids = [];

        $local_specs_query = $this->application->getSpecialities()
            ->with([
                'preference.documentType',
                'preference.privilege',
                'preference.specialMark',
                'targetReception.documentType',
                'speciality.profileRef',
                'speciality.educationSourceRef',
                'speciality.budgetLevelRef',
                'admissionCategory',
            ]);

        $raw_data = array_filter($raw_data);

        usort($raw_data, function ($a, $b) {
            
            if ($a['EnrollmentPriority'] === $b['EnrollmentPriority']) {
                return (int)$a['RowNumber'] <=> (int)$b['RowNumber'];
            }
            return (int)$a['EnrollmentPriority'] <=> (int)$b['EnrollmentPriority'];
        });
        $previous_inner_priority = [];
        $entranceTestsFrom1C = [];
        foreach ($raw_data as $raw_speciality) {
            $local_dict_spec = Speciality::GetFromRaw(
                ReferenceTypeManager::GetOrCreateReference(
                    StoredAdmissionCampaignReferenceType::class,
                    $raw_speciality['CampaignRef'] ?? null
                ),
                ReferenceTypeManager::GetOrCreateReference(
                    StoredCompetitiveGroupReferenceType::class,
                    $raw_speciality['CompetitiveGroupRef'] ?? null
                ),
                ReferenceTypeManager::GetOrCreateReference(
                    StoredCurriculumReferenceType::class,
                    $raw_speciality['CurriculumRef'] ?? null
                ),
                ReferenceTypeManager::GetOrCreateReference(
                    StoredProfileReferenceType::class,
                    $raw_speciality['ProfileRef'] ?? null
                ),
                ReferenceTypeManager::GetOrCreateReference(
                    StoredEducationSourceReferenceType::class,
                    $raw_speciality['EducationSourceRef'] ?? null
                ),
                ReferenceTypeManager::GetOrCreateReference(
                    StoredBudgetLevelReferenceType::class,
                    $raw_speciality['LevelBudgetRef'] ?? null
                )
            );

            $local_bach_spec = (clone $local_specs_query)
                ->andFilterWhere(['not', [BachelorSpeciality::tableName() . '.id' => $touched_speciality_ids]])
                ->andWhere(['speciality_id' => $local_dict_spec->id])
                ->one();

            if (empty($local_bach_spec)) {
                $local_bach_spec = new BachelorSpeciality();
                $local_bach_spec->application_id = $this->application->id;
                $local_bach_spec->speciality_id = $local_dict_spec->id;
            }
            $local_bach_spec->application_code = $raw_speciality['ApplicationStringGUID'];

            $local_bach_spec->is_enlisted = false;
            if (isset($raw_speciality['IsEnlisted'])) {
                $local_bach_spec->is_enlisted = (bool)$raw_speciality['IsEnlisted'];
            }

            $EntranceTests = ArrayHelper::getValue($raw_speciality, 'EntranceTests');
            if ($EntranceTests) {
                $entranceTestsFrom1C[] = [
                    'speciality' => $local_bach_spec,
                    'EntranceTests' => $EntranceTests
                ];
            }

            $local_bach_spec->admission_category_id = ArrayHelper::getValue(
                ReferenceTypeManager::GetOrCreateReference(
                    AdmissionCategory::class,
                    ArrayHelper::getValue($raw_speciality, 'AdmissionCategoryRef')
                ),
                'id'
            );

            if (!$local_bach_spec->save()) {
                throw new UserException("Не удалось обновить направления подготовки: " . print_r($local_bach_spec->errors, true));
            }
            
            $specialityEducationDocuments = ArrayHelper::getValue($raw_speciality, 'EducationDocuments');
            if (!is_array($specialityEducationDocuments)) {
                $specialityEducationDocuments = [$specialityEducationDocuments];
            }
            $educationsData = array_values(array_filter($all_educations, function ($edu) use ($specialityEducationDocuments) {
                return in_array($edu->tmp_uuid, $specialityEducationDocuments);
            }));
            foreach ($local_bach_spec->educationsData as $educationData) {
                $local_bach_spec->unlink('educationsData', $educationData, true);
            }
            foreach ($educationsData as $educationData) {
                $local_bach_spec->link('educationsData', $educationData);
            }

            $touched_speciality_ids[] = $local_bach_spec->id;
            $specialityGroup = $this->getSpecialityGroupByEducationSource($local_bach_spec);
            $specialityPriority = $local_bach_spec->specialityPriority;
            if (!$specialityPriority) {
                $specialityPriority = new SpecialityPriority();
                $specialityPriority->bachelor_speciality_id = $local_bach_spec->id;
            }
            $current_group_identifier = $this->specialityPrioritiesService->getSpecialityPriorityIdentifier($this->application, $local_bach_spec);
            $specialityPriority->priority_group_identifier = $current_group_identifier;
            $specialityPriority->enrollment_priority = $raw_speciality['EnrollmentPriority'];

            $inner_priority_key = "{$specialityGroup}.{$raw_speciality['EnrollmentPriority']}";
            $specialityPriority->inner_priority = ArrayHelper::getValue($previous_inner_priority, $inner_priority_key, 0) + 1;
            ArrayHelper::setValue($previous_inner_priority, $inner_priority_key, $specialityPriority->inner_priority);

            $specialityPriority->save(false);

            $agreed_value = BooleanCaster::cast(ArrayHelper::getValue($raw_speciality, 'Agreed', false));
            if ($agreed_value) {
                $admission_agreement = $local_bach_spec->agreement;
                if (!$admission_agreement) {
                    $admission_agreement = new AdmissionAgreement();
                    $admission_agreement->speciality_id = $local_bach_spec->id;
                }
                $admission_agreement->status = AdmissionAgreement::STATUS_VERIFIED;
                $admission_agreement->scenario = AdmissionAgreement::SCENARIO_RECOVER;

                if (!$admission_agreement->save()) {
                    throw new RecordNotValid($admission_agreement);
                }
                $admission_agreement->archiveAllDeclines();
            } elseif ($local_bach_spec->agreement) {
                $local_bach_spec->agreement->markToDelete(); 
            }
            $this->linkPreferenceAndTarget($raw_speciality, $local_bach_spec);

            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($local_bach_spec)
                ->update($raw_speciality['Files'] ?? []);
        }

        (new EntranceTestSetsFullPackageBuilder($this->application))->update($entranceTestsFrom1C);

        foreach ((clone $local_specs_query)->andFilterWhere(['not in', BachelorSpeciality::tableName() . '.id', $touched_speciality_ids])->all() as $spec_to_delete) {
            $spec_to_delete->delete();
        }
        return true;
    }

    protected function linkPreferenceAndTarget($raw_speciality, BachelorSpeciality $local_bach_spec): bool
    {
        $local_bach_spec->target_reception_id = null;
        $local_bach_spec->preference_id = null;
        $local_bach_spec->bachelor_olympiad_id = null;

        if (!EmptyCheck::isEmpty(ArrayHelper::getValue($raw_speciality, 'TargetTempGUID'))) {
            $matched_row = $this->findItemByColumn($this->raw_targets, 'TargetTempGUID', ArrayHelper::getValue($raw_speciality, 'TargetTempGUID'));
            if (!empty($matched_row)) {
                $local = (new TargetPackageBuilder($this->application))->findLocalByRaw($matched_row);
                $local_bach_spec->target_reception_id = ArrayHelper::getValue($local, 'id');
            }
        }
        $OlympicTempGUID = ArrayHelper::getValue($raw_speciality, 'OlympicTempGUID');
        if (!EmptyCheck::isEmpty($OlympicTempGUID)) {
            $matched_row = $this->findItemByColumn($this->raw_olympics, 'OlympicTempGUID', $OlympicTempGUID);
            if (!empty($matched_row)) {
                $local = (new OlympicPackageBuilder($this->application))->findLocalByRaw($matched_row);
                $local_bach_spec->bachelor_olympiad_id = ArrayHelper::getValue($local, 'id');
            }
        }
        if (!EmptyCheck::isEmpty(ArrayHelper::getValue($raw_speciality, 'BenefitTempGUID'))) {
            $matched_row = $this->findItemByColumn($this->raw_benefits, 'BenefitTempGUID', ArrayHelper::getValue($raw_speciality, 'BenefitTempGUID'));
            if (!empty($matched_row)) {
                $local = (new BenefitPackageBuilder($this->application))->findLocalByRaw($matched_row);
                $local_bach_spec->preference_id = ArrayHelper::getValue($local, 'id');
            }
        }

        $local_bach_spec->is_without_entrance_tests = boolval($OlympicTempGUID);

        if (!$local_bach_spec->save()) {
            throw new RecordNotValid($local_bach_spec);
        }
        return true;
    }

    protected function findItemByColumn(array $items, string $col_name, $col_value)
    {
        foreach ($items as $item) {
            $itemValue = ArrayHelper::getValue($item, $col_name);
            if ($itemValue == $col_value) {
                return $item;
            }
        }
        return null;
    }

    public function updateBenefits()
    {
        (new TargetPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->update($this->raw_targets);

        (new OlympicPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->update($this->raw_olympics);

        (new BenefitPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->update($this->raw_benefits);
    }
}
