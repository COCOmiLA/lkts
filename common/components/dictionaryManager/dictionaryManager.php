<?php

namespace common\components\dictionaryManager;

use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use backend\models\RBACAuthAssignment;
use Closure;
use common\components\AdmissionCampaignDictionaryManager\AdmissionCampaignDictionaryManager;
use common\components\AppUpdate;
use common\components\BooleanCaster;
use common\components\dictionaryManager\GetReferencesManager\GetContractorListManager;
use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\components\helpers\TableCreateHelper;
use common\components\PageRelationManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\soapException;
use common\models\AttachmentType;
use common\models\DebuggingSoap;
use common\models\dictionary\AdmissionBase;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\AdmissionProcedure;
use common\models\dictionary\AvailableDocumentTypesForConcession;
use common\models\dictionary\BudgetLevel;
use common\models\dictionary\Contractor;
use common\models\dictionary\Country;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\DictionaryPredmetOfExamsSchedule;
use common\models\dictionary\DictionaryReasonForExam;
use common\models\dictionary\DocumentShipment;
use common\models\dictionary\DocumentType;
use common\models\dictionary\DocumentTypeAttributeSetting;
use common\models\dictionary\DocumentTypePropertiesSetting;
use common\models\dictionary\EducationDataFilter;
use common\models\dictionary\EducationType;
use common\models\dictionary\FamilyType;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\Gender;
use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\Olympiad;
use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\OwnageForm;
use common\models\dictionary\Privilege;
use common\models\dictionary\Speciality;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\SpecialRequirementReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredContractorReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicClassReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicKindReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubjectSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\IndividualAchievementDocumentType;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\managers\BatchMaker;
use common\models\settings\CodeSetting;
use common\models\ToAssocCaster;
use common\models\User;
use common\modules\abiturient\models\AdditionalReceiptDateControl;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\AgreementCondition;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use common\modules\abiturient\models\bachelor\CgetChildSubject;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;
use common\modules\abiturient\models\bachelor\CgetRequiredPreference;
use common\modules\abiturient\models\PersonalData;
use League\CLImate\TerminalObject\Dynamic\Progress;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class dictionaryManager extends Component
{
    private function getSuccessAnswer()
    {
        return [1, []];
    }

    public function loadSpecialMarks(?Progress $progress = null)
    {
        try {
            $response = GetReferencesManager::getReferences(SpecialMark::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (empty($response->getReferences())) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = iterator_count($response->getReferences());
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_special_mark_ids = [];
            foreach ($response->getReferences() as $I => $reference) {
                if ($progress) {
                    $progress->current($I + 1);
                }
                $touched_special_mark_ids[] = ReferenceTypeManager::GetOrCreateReference(SpecialMark::class, $reference)->id;
            }
            SpecialMark::updateAll(['archive' => true], ['not', ['id' => $touched_special_mark_ids]]);
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadGender(?Progress $progress = null)
    {
        try {
            $response = GetReferencesManager::getReferences(Gender::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (empty($response->getReferences())) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = iterator_count($response->getReferences());
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_gender_ids = [];
            foreach ($response->getReferences() as $I => $reference) {
                if ($progress) {
                    $progress->current($I + 1);
                }

                $touched_gender_ids[] = ReferenceTypeManager::GetOrCreateReference(Gender::class, $reference)->id;
            }
            Gender::updateAll(
                ['archive' => true],
                ['not', ['id' => $touched_gender_ids]]
            );

            $query = PersonalData::find()
                ->where(['gender_id' => null])
                ->andWhere(['not', ['gender' => null]]);
            if ($query->exists()) {
                foreach ($query->all() as $I => $item) {
                    $gen = Gender::findOne([
                        'code' => $item->gender,
                        'archive' => false
                    ]);

                    if ($gen !== null) {
                        $item->gender_id = $gen->id;
                        $item->updateAttributes(['gender_id']);
                        $profile = ArrayHelper::getValue($item, 'abiturientQuestionary.user.userProfile');
                        if ($profile !== null) {
                            $profile->gender_id = $gen->id;
                            $profile->updateAttributes(['gender_id']);
                        }
                    }
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadAdmissionFeatures(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredDetailGroupReferenceType::class, null, null, null, $progress);
    }

    public function loadIndividualAchievement(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetAllIndividualExams', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if ($result === false) {
            return [0, []];
        }

        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error('Ошибка при выполнении метода GetAllIndividualExams: ' . $result->return->UniversalResponse->Description . ' ' . PHP_EOL . print_r($result, true));
            return [0, []];
        }

        if (!isset($result->return->Predmet)) {
            return [0, []];
        }

        if (is_array($result->return->Predmet) && !$result->return->Predmet) {
            return [0, []];
        }

        $cache_storage = [];


        if (!is_array($result->return->Predmet)) {
            $result->return->Predmet = [$result->return->Predmet];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->Predmet);
            $progress->total($progressCount);
        }
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $touched_ia_type_ids = [];

            foreach ($result->return->Predmet as $I => $ind_arch) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $ind_arch->Code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $ind_arch,
                    'SubjectRef',
                    'ReferenceId',
                    'Code'
                );
                $ind_arch->Name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $ind_arch,
                    'SubjectRef',
                    'ReferenceName',
                    'Name'
                );
                $ind_arch->IdPK = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $ind_arch,
                    'CampaignRef',
                    'ReferenceId',
                    'IdPK'
                );
                $individual_achievement = IndividualAchievementType::find()
                    ->where([
                        'code' => (string)$ind_arch->Code,
                        'campaign_code' => (string)$ind_arch->IdPK,
                    ]);
                $curriculumRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(StoredCurriculumReferenceType::class, $ind_arch->CurriculumRef, $cache_storage);
                if (isset($curriculumRefId)) {
                    $individual_achievement = $individual_achievement
                        ->andWhere(['ach_curriculum_ref_id' => $curriculumRefId]);
                }
                $individual_achievement = $individual_achievement->one();

                if ($individual_achievement == null) {
                    $individual_achievement = new IndividualAchievementType();
                }
                $individual_achievement->scenario = IndividualAchievementType::$SCENARIO_WITHOUT_EXISTS_CHECK;

                $individual_achievement->archive = false;
                $individual_achievement->code = (string)$ind_arch->Code;
                $individual_achievement->campaign_code = (string)$ind_arch->IdPK;
                $individual_achievement->name = (string)$ind_arch->Name;
                $individual_achievement->points_in_group_are_awarded_once = BooleanCaster::cast($ind_arch->PointsInGroupAreAwardedOnce ?? false);
                $individual_achievement->loadRefKeysWithCaching($ind_arch, true, $cache_storage);
                if (isset($curriculumRefId)) {
                    $individual_achievement->ach_curriculum_ref_id = $curriculumRefId;
                }

                if ($individual_achievement->validate()) {
                    $individual_achievement->save(false);
                    $touched_ia_type_ids[] = $individual_achievement->id;
                } else {
                    throw new RecordNotValid($individual_achievement);
                }
            }
            IndividualAchievementType::updateAll(['archive' => true], ['not', ['id' => $touched_ia_type_ids]]);
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadForeignLanguages(?Progress $progress = null)
    {
        try {
            $response = GetReferencesManager::getReferences(ForeignLanguage::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (empty($response->getReferences())) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = iterator_count($response->getReferences());
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $touched_foreign_language_ids = [];
            foreach ($response->getReferences() as $I => $reference) {
                if ($progress) {
                    $progress->current($I + 1);
                }
                $touched_foreign_language_ids[] = ReferenceTypeManager::GetOrCreateReference(ForeignLanguage::class, $reference)->id;
            }
            ForeignLanguage::updateAll(['archive' => true], ['not', ['id' => $touched_foreign_language_ids]]);

            
            $query = PersonalData::find()->where(['language_id' => null])->andWhere([
                'not', ['language_code' => null]
            ]);
            if ($query->exists()) {
                foreach ($query->all() as $item) {

                    $lang = ForeignLanguage::findOne([
                        'code' => $item->language_code,
                        'archive' => false
                    ]);

                    if ($lang) {
                        $item->language_id = $lang->id;
                        $item->updateAttributes(['language_id']);
                    }
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadEducationTypes(?Progress $progress = null)
    {
        try {
            $responses = GetReferencesManager::getReferences(EducationType::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        $references = $responses->getReferences();

        if (empty($references)) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $references = iterator_to_array($references);
            $progressCount = count($references);
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_edu_type_ids = [];

            foreach ($references as $I => $reference) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $touched_edu_type_ids[] = ReferenceTypeManager::GetOrCreateReference(EducationType::class, $reference)->id;
            }
            EducationType::updateAll(['archive' => true], ['not', ['id' => $touched_edu_type_ids]]);
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    protected function getGetEntrantTestSetsGenerator()
    {
        foreach ($this->runMethodWithBatchLoad('GetEntrantTestSets') as $I => $result) {
            if ($result && isset($result->return) && isset($result->return->CompetitiveGroupEntranceTest)) {
                if (!is_array($result->return->CompetitiveGroupEntranceTest)) {
                    $result->return->CompetitiveGroupEntranceTest = [$result->return->CompetitiveGroupEntranceTest];
                }
                foreach ($result->return->CompetitiveGroupEntranceTest as $I => $item) {
                    yield $item;
                }
            }
        }
    }

    public function loadDictionaryCompetitiveGroupEntranceTests(?Progress $progress = null)
    {
        return (new dictionaryManagerDictionaryCompetitiveGroupEntranceTests)->loadDictionary($progress);
    }

    public function GetInterfaceVersion(string $method_name): ?string
    {
        $soapManager = \Yii::$app->soapClientAbit;
        if (in_array($method_name, ['PostEntrantPackage', 'GetEntrantProfilePackage', 'GetEntrantPackage', 'PostFilesList', 'GetFilesList'])) {
            $soapManager = \Yii::$app->soapClientWebApplication;
        }
        return Yii::$app->cache->getOrSet('GetInterfaceVersion' . $method_name, function () use ($soapManager, $method_name) {
            try {
                $response = $soapManager->load_with_caching('GetInterfaceVersion', [
                    'Name' => $method_name
                ]);
                return $response->return;
            } catch (Throwable $e) {
                \Yii::error("Не удалось получить версию метода {$method_name}: {$e->getMessage()}");
            }
            return '0.0.0.0';
        }, 3600);
    }

    


    public function fetchSpecialities()
    {
        foreach ($this->runMethodWithBatchLoad('GetStringsPriema') as $result) {
            if ($result && $result->return) {
                if (isset($result->return->StringsPlanPriema) && isset($result->return->StringsPlanPriema->StringPlanPriema)) {
                    if (!is_array($result->return->StringsPlanPriema->StringPlanPriema)) {
                        $result->return->StringsPlanPriema->StringPlanPriema = [$result->return->StringsPlanPriema->StringPlanPriema];
                    }
                    foreach ($result->return->StringsPlanPriema->StringPlanPriema as $item) {
                        yield $item;
                    }
                }
            }
        }
    }

    public function loadSpecialities(?Progress $progress = null)
    {
        $StringsPlanPriema = $this->fetchSpecialities();

        $refCacheList = [];
        $allRefsCacheList = [];
        $progressCount = 0;
        if ($progress) {
            $StringsPlanPriema = iterator_to_array($StringsPlanPriema);
            $progressCount = count($StringsPlanPriema);
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $touched_speciality_ids = [];
            foreach ($StringsPlanPriema as $I => $StringPlanPriema) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }
                $spec = Speciality::findOne(
                    ArrayHelper::merge([
                        'faculty_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'SubdivisionRef',
                            'ReferenceId',
                            'FacultetCode'
                        ),
                        'speciality_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'DirectionRef',
                            'ReferenceId',
                            'SpecialityCode'
                        ),
                        'profil_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'ProfileRef',
                            'ReferenceId',
                            'ProfilCode'
                        ),
                        'edulevel_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'EducationLevelRef',
                            'ReferenceId',
                            'EducationLevelCode'
                        ),
                        'eduform_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'EducationFormRef',
                            'ReferenceId',
                            'EducationFormCode'
                        ),
                        'eduprogram_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'EducationProgramRef',
                            'ReferenceId',
                            'EducationProgramCode'
                        ),
                        'finance_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'EducationSourceRef',
                            'ReferenceId',
                            'FinanceCode'
                        ),
                        'group_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'CompetitiveGroupRef',
                            'ReferenceId',
                            'GroupCode'
                        ),
                        'speciality_human_code' => (string)$StringPlanPriema->SpecialityCodeOKSO,
                        'campaign_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'CampaignRef',
                            'ReferenceId',
                            'IdPK'
                        ),
                        'detail_group_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'DetailGroupRef',
                            'ReferenceId',
                            'DetailGroupCode'
                        ),
                        'budget_level_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                            $StringPlanPriema,
                            'LevelBudgetRef',
                            'ReferenceId',
                            'BudgetLevelCode'
                        ),
                        'special_right' => (bool)$StringPlanPriema->SpecialRight,
                    ], Speciality::getReferenceTypeSearchArray($StringPlanPriema, $refCacheList, $allRefsCacheList))
                );
                if (!$spec) {
                    $spec = new Speciality();
                }
                $spec->scenario = Speciality::$SCENARIO_WITHOUT_EXISTS_CHECK;

                $spec->campaign_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'CampaignRef',
                    'ReferenceId',
                    'IdPK'
                );
                $spec->group_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'CompetitiveGroupRef',
                    'ReferenceName',
                    'GroupName'
                );
                $spec->group_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'CompetitiveGroupRef',
                    'ReferenceId',
                    'GroupCode'
                );
                $spec->profil_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'ProfileRef',
                    'ReferenceId',
                    'ProfilCode'
                );
                $spec->profil_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'ProfileRef',
                    'ReferenceName',
                    'ProfilName'
                );
                $spec->finance_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationSourceRef',
                    'ReferenceId',
                    'FinanceCode'
                );
                $spec->finance_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationSourceRef',
                    'ReferenceName',
                    'FinanceName'
                );
                $spec->faculty_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'SubdivisionRef',
                    'ReferenceId',
                    'FacultetCode'
                );
                $spec->faculty_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'SubdivisionRef',
                    'ReferenceName',
                    'FacultetName'
                );
                $spec->eduform_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationFormRef',
                    'ReferenceId',
                    'EducationFormCode'
                );
                $spec->eduform_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationFormRef',
                    'ReferenceName',
                    'EducationFormName'
                );
                $spec->speciality_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'DirectionRef',
                    'ReferenceId',
                    'SpecialityCode'
                );
                $spec->speciality_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'DirectionRef',
                    'ReferenceName',
                    'SpecialityName'
                );
                $spec->edulevel_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationLevelRef',
                    'ReferenceId',
                    'EducationLevelCode'
                );
                $spec->edulevel_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationLevelRef',
                    'ReferenceName',
                    'EducationLevelName'
                );
                $spec->detail_group_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'DetailGroupRef',
                    'ReferenceId',
                    'DetailGroupCode'
                );
                $spec->detail_group_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'DetailGroupRef',
                    'ReferenceName',
                    'DetailGroupName'
                );
                $spec->eduprogram_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationProgramRef',
                    'ReferenceId',
                    'EducationProgramCode'
                );
                $spec->eduprogram_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'EducationProgramRef',
                    'ReferenceName',
                    'EducationProgramName'
                );
                $spec->speciality_human_code = (string)$StringPlanPriema->SpecialityCodeOKSO;
                $spec->budget_level_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'LevelBudgetRef',
                    'ReferenceId',
                    'BudgetLevelCode'
                );
                $spec->budget_level_name = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField(
                    $StringPlanPriema,
                    'LevelBudgetRef',
                    'ReferenceName',
                    'BudgetLevelName'
                );
                $spec->special_right = (bool)$StringPlanPriema->SpecialRight;
                $spec->receipt_allowed = (bool)$StringPlanPriema->ReceiptAllow;
                $spec->is_combined_competitive_group = (bool)$StringPlanPriema->CombinedCompetitiveGroup;

                $spec->archive = false;

                $spec->loadRefKeysWithCaching($StringPlanPriema, true, $refCacheList);

                if (!$spec->save()) {
                    throw new RecordNotValid($spec);
                }
                $touched_speciality_ids[] = $spec->id;
                unset($StringPlanPriema);
            }
            Speciality::updateAll(['archive' => true], ['not', ['id' => $touched_speciality_ids]]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }


    




    public function fetchAdmissionCampaigns(&$Campaigns)
    {
        try {
            $result = AdmissionCampaignDictionaryManager::FetchAdmissionCampaign();
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if ($result === false) {
            return [0, []];
        }

        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error('Ошибка при выполнении метода GetPK: ' . $result->return->UniversalResponse->Description . ' ' . PHP_EOL . print_r($result, true));
            return [0, []];
        }

        if (!isset($result->return->PK)) {
            return [0, []];
        }

        if (is_array($result->return->PK) && !$result->return->PK) {
            return [0, []];
        }

        $admissionCampaigns = $result->return->PK;

        if (!is_array($admissionCampaigns)) {
            $admissionCampaigns = [$admissionCampaigns];
        }

        $Campaigns = $admissionCampaigns;

        return true;
    }

    public function loadAdmissionCampaigns(?Progress $progress = null)
    {
        $status = $this->fetchAdmissionCampaigns($admissionCampaignsFrom1C);
        $progressCount = 0;
        if ($progress && $admissionCampaignsFrom1C) {
            $progressCount = count($admissionCampaignsFrom1C);
            $progress->total($progressCount);
        }

        if ($status !== true) {
            return $status;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_record_ids = [];

            foreach ($admissionCampaignsFrom1C as $I => $campaign) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $admission_campaign = AdmissionCampaignDictionaryManager::FindAdmissionCampaign($campaign);

                if ($admission_campaign == null) {
                    $admission_campaign = new AdmissionCampaign();
                }
                $admission_campaign->code = (string)$campaign->IdPK;
                $admission_campaign->api_token = (string)$campaign->CampaignToken;
                $admission_campaign->archive = false;
                $admission_campaign->name = (string)$campaign->Description;
                $admission_campaign->reception_allowed = (int)$campaign->ReceptionAllowed;
                $admission_campaign->limit_type = (string)$campaign->MaximalSpecialityType;
                $admission_campaign->snils_is_required = (bool)$campaign->CheckSNILS;
                $admission_campaign->max_speciality_count = (int)$campaign->MaximalSpeciality;
                $admission_campaign->consents_allowed = (int)$campaign->AddModifyConsentsAllowed;
                $admission_campaign->multiply_applications_allowed = (int)$campaign->IsAllowedMultipleApplicationsToOneGroup;
                $admission_campaign->count_target_specs_separately = BooleanCaster::cast($campaign->CountAllDirectionsSeparatelyOnTargetQuotasRegardlessOfMultiprofileCompetition ?? false);
                $admission_campaign->require_previous_passport = BooleanCaster::cast($campaign->RequirePreviousPassports ?? false);
                $admission_campaign->allow_multiply_education_documents = BooleanCaster::cast($campaign->AllowMultiplyEducationDocuments ?? false);
                $admission_campaign->common_education_document = BooleanCaster::cast($campaign->CommonEducationDocument ?? false);
                $admission_campaign->separate_statement_for_full_payment_budget = BooleanCaster::cast($campaign->SeparateSpecialitiesForBudgetAndFullPayment ?? false);
                $admission_campaign->use_common_agreements = BooleanCaster::cast($campaign->UseCommonAgreements ?? false);

                if (!empty($campaign->CampaignRef)) {
                    $admission_campaign->loadRefKey($campaign->CampaignRef);
                }

                if (!$admission_campaign->save()) {
                    throw new RecordNotValid($admission_campaign);
                }

                $touched_record_ids[] = $admission_campaign->id;

                if (isset($campaign->PriorityConditions)) {
                    $conditions = [];
                    if (is_array($campaign->PriorityConditions)) {
                        $conditions = $campaign->PriorityConditions;
                    } else {
                        if (isset($campaign->PriorityConditions->ConditionRef)) {
                            if (is_array($campaign->PriorityConditions->ConditionRef)) {
                                $conditions = $campaign->PriorityConditions->ConditionRef;
                            } else {
                                $conditions[] = $campaign->PriorityConditions->ConditionRef;
                            }
                        } else {
                            $conditions[] = $campaign->PriorityConditions;
                        }
                    }
                    $old_mode_names = ArrayHelper::getColumn($admission_campaign->specialityGroupingModes, 'code_name');
                    $needs_reset_priorities = false;
                    foreach ($conditions as $condition) {
                        $conditionRef = $condition;
                        if (isset($condition->ConditionRef)) {
                            $conditionRef = $condition->ConditionRef;
                        }
                        $code_name = (string)$conditionRef->PredefinedDataName ?? null;
                        $description = (string)$conditionRef->ReferenceName ?? null;
                        if ($code_name && $description) {
                            $new_mode = $admission_campaign->addSpecialityGroupingMode($code_name, $description);
                            if (!in_array($new_mode->code_name, $old_mode_names)) {
                                $needs_reset_priorities = true;
                            }
                        }
                    }
                    if ($needs_reset_priorities) {
                        $admission_campaign->resetComputedSpecialityGroupingPriorities();
                    }
                }

                $this->updateAgreementConditions($campaign, $admission_campaign);
            }
            AdmissionCampaign::updateAll(['archive' => true], ['not', ['id' => $touched_record_ids]]);
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    protected function updateAgreementConditions($raw_campaign, AdmissionCampaign $admission_campaign)
    {
        $touched_agreement_conditions = [];

        if (isset($raw_campaign->AgreementConditions)) {
            $agreement_conditions = [];
            if (is_array($raw_campaign->AgreementConditions)) {
                $agreement_conditions = $raw_campaign->AgreementConditions;
            } else {
                if (isset($raw_campaign->AgreementConditions->EducationSourceRef)) {
                    if (is_array($raw_campaign->AgreementConditions->EducationSourceRef)) {
                        $agreement_conditions = $raw_campaign->AgreementConditions->EducationSourceRef;
                    } else {
                        $agreement_conditions[] = $raw_campaign->AgreementConditions->EducationSourceRef;
                    }
                } else {
                    $agreement_conditions[] = $raw_campaign->AgreementConditions;
                }
            }

            foreach ($agreement_conditions as $agreement_condition) {
                $conditionRef = $agreement_condition;
                if (isset($agreement_condition->EducationSourceRef)) {
                    $conditionRef = $agreement_condition->EducationSourceRef;
                }

                $edu_source_ref  = ReferenceTypeManager::GetOrCreateReference(
                    StoredEducationSourceReferenceType::class,
                    $conditionRef
                );

                $local_agreement_condition = AgreementCondition::find()->andWhere([
                    'campaign_id' => $admission_campaign->id,
                    'education_source_ref_id' => $edu_source_ref->id
                ])->one();

                if ($local_agreement_condition === null) {
                    $local_agreement_condition = new AgreementCondition();
                    $local_agreement_condition->campaign_id = $admission_campaign->id;
                }

                $local_agreement_condition->archive = false;
                $local_agreement_condition->education_source_ref_id = $edu_source_ref->id;
                if (!$local_agreement_condition->save()) {
                    throw new RecordNotValid($local_agreement_condition);
                }
                $touched_agreement_conditions[] = $local_agreement_condition->id;
            }
        }

        AgreementCondition::updateAll(['archive' => true], [
            'and',
            [
                'not in', 'id', $touched_agreement_conditions,
            ],
            [
                'campaign_id' => $admission_campaign->id
            ]
        ]);
    }

    public function loadCampaignInfo(?Progress $progress = null)
    {
        try {
            $user = Yii::$app->user->identity;
        } catch (Throwable $th) {
            $tnUser = User::tableName();
            $tnRbac = RBACAuthAssignment::tableName();
            $user = User::find()
                ->leftJoin($tnRbac, "{$tnUser}.id = {$tnRbac}.user_id")
                ->where(["{$tnRbac}.item_name" => User::ROLE_ADMINISTRATOR])
                ->orderBy('id')
                ->one();
        }
        $campaigns = AdmissionCampaign::findAll(['archive' => false, 'reception_allowed' => 1]);
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($campaigns);
            $progress->total($progressCount);
        }
        CampaignInfo::updateAll(['detail_group_code' => ''], ['detail_group_code' => '0']);

        $cache_storage = [];
        $allRefsCacheList = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_campaign_ids = [];

            foreach ($campaigns as $I => $campaign) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $applicationType = ApplicationType::findOne(['campaign_id' => $campaign->id, 'archive' => false]);
                if ($applicationType) {

                    ApplicationTypeHistory::createNewEntry(
                        $user,
                        ApplicationTypeHistory::UPDATE_CAMPAIGN_INFO_DICTIONARY,
                        $applicationType->id
                    );
                }
                try {
                    $result = Yii::$app->soapClientAbit->load('GetPKInfo', [
                        'IdPK' => $campaign->code,
                        'CampaignRef' => ReferenceTypeManager::GetReference($campaign, 'referenceType')
                    ], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
                } catch (Throwable $e) {
                    return [-1, $e];
                }

                if ($result === false) {
                    continue;
                }

                if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
                    Yii::error('Ошибка при выполнении метода GetPKInfo: ' . $result->return->UniversalResponse->Description . ' ' . PHP_EOL . print_r($result, true));
                }

                if (!isset($result->return->Stage)) {
                    continue;
                }

                if (is_array($result->return->Stage) && !$result->return->Stage) {
                    continue;
                }

                if (!is_array($result->return->Stage)) {
                    $result->return->Stage = [$result->return->Stage];
                }

                foreach ($result->return->Stage as $info) {
                    $campaign_info = null;

                    $searchArrayOData = [];
                    $admissionCategoryId = null;
                    if (isset($info->AdmissionCategoryRef)) {
                        $admissionCategoryId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(AdmissionCategory::class, $info->AdmissionCategoryRef, $cache_storage);
                        if ($admissionCategoryId) {
                            $searchArrayOData['admission_category_id'] = $admissionCategoryId;
                        }
                    }

                    $campaign_info = CampaignInfo::find()
                        ->where(
                            ArrayHelper::merge([
                                'campaign_id' => $campaign->id,
                                'finance_code' => (string)$info->Finance,
                                'eduform_code' => (string)$info->EducationForm,
                                'detail_group_code' => (string)($info->DetailGroupCode == '0' ? '' : $info->DetailGroupCode),
                                'category_code' => (string)$info->CategoryCode,
                            ], CampaignInfo::getReferenceTypeSearchArray($info, $cache_storage, $allRefsCacheList), $searchArrayOData)
                        )
                        ->andWhere(['not', ['id' => $touched_campaign_ids]])
                        ->one();

                    if ($campaign_info == null) {
                        $campaign_info = new CampaignInfo();
                    }
                    $campaign_info->campaign_id = $campaign->id;
                    $campaign_info->finance_code = (string)$info->Finance;
                    $campaign_info->eduform_code = (string)$info->EducationForm;
                    $campaign_info->date_start = date('Y-m-d H:i:s', strtotime((string)$info->DateStart));
                    $campaign_info->date_final = date('Y-m-d H:i:s', strtotime((string)$info->DateFinal));
                    $campaign_info->date_order_start = date('Y-m-d H:i:s', strtotime((string)$info->DateStartOrder));
                    $campaign_info->date_order_end = date('Y-m-d 23:59:59', strtotime((string)$info->DateFinalOrder));
                    $campaign_info->category_code = (string)$info->CategoryCode;
                    $campaign_info->archive = false;
                    $campaign_info->detail_group_code = (string)($info->DetailGroupCode == '0' ? '' : $info->DetailGroupCode);
                    if ($admissionCategoryId) {
                        $campaign_info->admission_category_id = $admissionCategoryId;
                    }
                    $campaign_info->loadRefKeysWithCaching($info, true, $cache_storage);

                    if ($campaign_info->validate()) {
                        $campaign_info->save(false);
                        $touched_campaign_ids[] = $campaign_info->id;
                        if (isset($info->CampaignEventPeriods) && !EmptyCheck::isEmpty($info->CampaignEventPeriods)) {
                            if (!is_array($info->CampaignEventPeriods->CampaignEventPeriod ?? [])) {
                                $info->CampaignEventPeriods->CampaignEventPeriod = array_values(array_filter([$info->CampaignEventPeriods->CampaignEventPeriod]));
                            }
                            $campaign_info->updatePeriods($info->CampaignEventPeriods->CampaignEventPeriod ?? []);
                        }
                    } else {
                        throw new RecordNotValid($campaign_info);
                    }
                }
            }
            CampaignInfo::updateAll(['archive' => true], ['not', ['id' => $touched_campaign_ids]]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadGetOlympiad(?Progress $progress = null)
    {
        $key_names = [
            'code',
            'type',
            'place',
            'level',
            'class',
            'education_class',
            'kind',
            'profile',
            'need_ege',
            'name',
            'year',
            'ref_id',
            'olympic_type_ref_id',
            'olympic_level_ref_id',
            'olympic_kind_ref_id',
            'olympic_class_ref_id',
            'olympic_profile_ref_id',
        ];
        sort($key_names);
        try {
            $result = Yii::$app->soapClientAbit->load('GetAllOlympiads', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }
        if ($result === false || !isset($result->return->AllOlympiads)) {
            return [0, []];
        }
        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error("Ошибка при выполнении метода GetCatalogOlympiad: {$result->return->UniversalResponse->Description} " . PHP_EOL . print_r($result, true));
            return [0, []];
        }
        $cache_storage = [];

        if (!is_array($result->return->AllOlympiads)) {
            $result->return->AllOlympiads = [$result->return->AllOlympiads];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->AllOlympiads);
            $progress->total($progressCount);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_ids = [];
            foreach ($result->return->AllOlympiads as $I => $olimpic) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $attributes = [];
                $attributes['code'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicRef', 'ReferenceId', 'Code');
                $attributes['type'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicTypeRef', 'ReferenceName', 'Type');
                $attributes['place'] = (string)$olimpic->Place;
                $attributes['level'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicLevelRef', 'ReferenceName', 'Level');
                $attributes['class'] = (int)ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicClassRef', 'ReferenceName', 'Class');
                $attributes['education_class'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicClassRef', 'ReferenceName', 'Class');
                $attributes['kind'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicKindRef', 'ReferenceName', 'Kind');
                $attributes['profile'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olimpic, 'OlympicProfileRef', 'ReferenceName', 'Profile');
                $attributes['need_ege'] = (bool)$olimpic->ConfirmationEGE;
                $attributes['name'] = (string)$olimpic->Name;
                $attributes['year'] = substr((string)$olimpic->Date, 0, 4) != '0001' ? date('Y', strtotime((string)$olimpic->Date)) : 'Не указан';

                $all_props = ArrayHelper::merge((new Olympiad())->loadRefKeysWithCaching($olimpic, true, $cache_storage)->attributes, $attributes);
                $attributes = array_intersect_key($all_props, array_flip($key_names));

                $local = Olympiad::find()->andWhere($attributes)->one();
                if (!$local) {
                    $local = new Olympiad();
                    $local->attributes = $attributes;
                    if (!$local->save()) {
                        throw new RecordNotValid($local);
                    }
                }
                $touched_ids[] = $local->id;
            }
            BachelorPreferences::updateAll(['olympiad_id' => null], ['not', ['olympiad_id' => $touched_ids]]);
            OlympiadFilter::updateAll(['olympiad_id' => null], ['not', ['olympiad_id' => $touched_ids]]);
            Olympiad::deleteAll(['not', ['id' => $touched_ids]]);

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }

            $this->loadOneReferenceDictionary(StoredOlympicTypeReferenceType::class, null, null, null, $progress);
            $this->loadOneReferenceDictionary(StoredOlympicLevelReferenceType::class, null, null, null, $progress);
            $this->loadOneReferenceDictionary(StoredOlympicKindReferenceType::class, null, null, null, $progress);
            $this->loadOneReferenceDictionary(StoredOlympicClassReferenceType::class, null, null, null, $progress);
            $this->loadOneReferenceDictionary(StoredOlympicProfileReferenceType::class, null, null, null, $progress);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    protected function runMethodWithBatchLoad(string $method_name)
    {
        $admission_campaigns = AdmissionCampaign::find()->active()->all();
        foreach ($admission_campaigns as $admission_campaign) {
            $competitiveGroups = $admission_campaign->getCompetitiveGroups()->all();
            $competitiveGroups = array_chunk($competitiveGroups, $this->getDictionaryBatchSize());
            foreach ($competitiveGroups as $competitiveGroupChunk) {
                $result = Yii::$app->soapClientAbit->load(
                    $method_name,
                    [
                        'CampaignRef' => ReferenceTypeManager::GetReference($admission_campaign->referenceType),
                        'CompetitiveGroupRefs' => [
                            'CompetitiveGroupRef' => array_map(
                                function ($competitiveGroup) {
                                    return ReferenceTypeManager::GetReference($competitiveGroup);
                                },
                                $competitiveGroupChunk
                            )
                        ]
                    ],
                    DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled
                );
                yield $result;
            }
        }
    }

    protected function getGetOlympiadGenerator()
    {
        foreach ($this->runMethodWithBatchLoad('GetOlympiad') as $result) {
            if (isset($result->return->Olympiad)) {
                if (!is_array($result->return->Olympiad)) {
                    $result->return->Olympiad = [$result->return->Olympiad];
                }
                foreach ($result->return->Olympiad as $item) {
                    yield $item;
                }
            }
        }
    }

    public function loadGetOlympiadFilter(?Progress $progress = null)
    {
        $key_names = [
            'id_pk',
            'olympiad_code',
            'specific_mark_code',
            'campaign_ref_id',
            'special_mark_id',
            'olympiad_id',
            'curriculum_ref_id',
            'variant_of_retest_ref_id',
        ];
        sort($key_names);
        $OlympiadGenerator = $this->getGetOlympiadGenerator();
        $progressCount = 0;
        if ($progress) {
            $OlympiadGenerator = iterator_to_array($OlympiadGenerator);
            $progressCount = count($OlympiadGenerator);
            $progress->total($progressCount);
        }
        $cache_storage = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            OlympiadFilter::deleteAll();
            foreach ($OlympiadGenerator as $I => $olympic) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }
                $buffer = [];
                $buffer['id_pk'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olympic, 'CampaignRef', 'ReferenceId', 'IdPK');
                $buffer['olympiad_code'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olympic, 'OlympicRef', 'ReferenceId', 'OlympiadCode');
                $buffer['specific_mark_code'] = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($olympic, 'SpecialMarkRef', 'ReferenceId', 'SpecialMarkCode');

                $all_props = ArrayHelper::merge((new OlympiadFilter())->loadRefKeysWithCaching($olympic, true, $cache_storage)->attributes, $buffer);
                $buffer = array_intersect_key($all_props, array_flip($key_names));

                try {
                    Yii::$app->db->createCommand()->insert(
                        'dictionary_olympiads_filter',
                        $buffer
                    )->execute();
                } catch (Throwable $e) {
                    Yii::error("Ошибка заполнения словаря GetOlympiad: {$e->getMessage()}" . PHP_EOL . print_r($buffer, true));
                    throw $e;
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadGetAdmissionProcedures(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetAllAdmissionProcedures', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if ($result === false || !isset($result->return->AdmissionProcedures)) {
            return [0, []];
        }
        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error("Ошибка при выполнении метода GetPreferences: {$result->return->UniversalResponse->Description} " . PHP_EOL . print_r($result, true));
            return [0, []];
        }
        $cache_storage = [];
        $allRefsCacheList = [];
        if (!is_array($result->return->AdmissionProcedures)) {
            $result->return->AdmissionProcedures = [$result->return->AdmissionProcedures];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->AdmissionProcedures);
            $progress->total($progressCount);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_proc_ids = [];
            foreach ($result->return->AdmissionProcedures as $I => $admissionProcedure) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $pref = AdmissionProcedure::findOne(ArrayHelper::merge([
                    'id_pk' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'CampaignRef', 'ReferenceId', 'IdPK'),
                    'category_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'AdmissionCategoryRef', 'ReferenceId', 'CategoryCode'),
                    'finance_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'EducationSourceRef', 'ReferenceId', 'FinanceCode'),
                    'privilege_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'BenefitRef', 'ReferenceId', 'PrivilegeCode'),
                    'special_mark_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'SpecialMarkRef', 'ReferenceId', 'SpecialMarkCode'),
                    'individual_value' => (bool)$admissionProcedure->IndividualValue,
                    'priority_right' => (bool)$admissionProcedure->PriorityRight
                ], AdmissionProcedure::getReferenceTypeSearchArray($admissionProcedure, $cache_storage, $allRefsCacheList)));

                if ($pref == null) {
                    $pref = new AdmissionProcedure();
                    $pref->attributes = [
                        'id_pk' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'CampaignRef', 'ReferenceId', 'IdPK'),
                        'category_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'AdmissionCategoryRef', 'ReferenceId', 'CategoryCode'),
                        'finance_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'EducationSourceRef', 'ReferenceId', 'FinanceCode'),
                        'privilege_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'BenefitRef', 'ReferenceId', 'PrivilegeCode'),
                        'special_mark_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($admissionProcedure, 'SpecialMarkRef', 'ReferenceId', 'SpecialMarkCode'),
                        'individual_value' => (bool)$admissionProcedure->IndividualValue,
                        'priority_right' => (bool)$admissionProcedure->PriorityRight
                    ];
                    $pref->loadRefKeysWithCaching($admissionProcedure, true, $cache_storage);
                } else {
                    $pref->archive = false;
                }
                $pref->scenario = AdmissionProcedure::$SCENARIO_WITHOUT_EXISTS_CHECK;
                if (!$pref->save()) {
                    throw new RecordNotValid($pref);
                }
                $touched_proc_ids[] = $pref->id;
            }
            AdmissionProcedure::updateAll(['archive' => true], ['not', ['id' => $touched_proc_ids]]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadGetAllDocumentTypesAbiturient(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetAllDocumentTypesAbiturient', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error("Ошибка при выполнении метода GetAvailableDocumentTypesForConcession : {$result->return->UniversalResponse->Description} " . PHP_EOL . print_r($result, true));
            $exceptionFrom1C = new soapException('Ошибка обновления из 1с', '110022', 'GetAvailableDocumentTypesForConcession', $result->return->UniversalResponse->Description);
            return [-1, $exceptionFrom1C];
        }

        if ($result === false || !isset($result->return->DocumentTypeAbiturient)) {
            return [0, []];
        }

        $cache_storage = [];
        $allRefsCacheList = [];

        if (!is_array($result->return->DocumentTypeAbiturient)) {
            $result->return->DocumentTypeAbiturient = [$result->return->DocumentTypeAbiturient];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->DocumentTypeAbiturient);
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_att_type_ids = [];
            foreach ($result->return->DocumentTypeAbiturient as $I => $type) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }
                $attachment_type = AttachmentType::find()
                    ->andWhere(['from1c' => true])
                    ->andWhere(AttachmentType::getReferenceTypeSearchArray($type, $cache_storage, $allRefsCacheList))
                    ->one();

                if ($attachment_type == null) {
                    $attachment_type = new AttachmentType();
                    $attachment_type->hidden = false;
                }

                if (isset($type->DocumentTypeRef) && !ReferenceTypeManager::isReferenceTypeEmpty($type->DocumentTypeRef)) {
                    $document_type = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $type->DocumentTypeRef);
                } else {
                    $document_type = DocumentType::find()
                        ->where(['code' => $type->DocumentTypeCode])
                        ->active()
                        ->one();
                }
                $attachment_type->attributes = [
                    'document_type_guid' => $document_type ? $document_type->ref_key : '',
                    'name' => $document_type ? $document_type->description : '',
                    'required' => $type->ScanRequired == 'true' || (bool)$type->NeedOneOfDocuments,
                    'from1c' => true,
                    'need_one_of_documents' => (bool)$type->NeedOneOfDocuments,
                    'campaign_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($type, 'CampaignRef', 'ReferenceId', 'idPK'),
                    'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                    'document_type' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($type, 'DocumentTypeRef', 'ReferenceId', 'DocumentTypeCode'),
                    'document_set_code' => ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($type, 'DocumentSetRef', 'ReferenceId', 'DocumentSetCode'),
                    'is_using' => true
                ];
                $attachment_type->loadRefKeysWithCaching($type, true, $cache_storage);

                if ($attachment_type->validate()) {
                    $attachment_type->save(false);
                    $touched_att_type_ids[] = $attachment_type->id;
                } else {
                    throw new RecordNotValid($attachment_type);
                }
            }

            AttachmentType::updateAll(['is_using' => false], [
                'and',
                ['from1c' => true],
                ['not', ['id' => $touched_att_type_ids]]
            ]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadGetAvailableDocumentTypes(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetAvailableDocumentTypes', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if ($result === false || !isset($result->return->CampaignAvailableDocumentTypes)) {
            return [0, []];
        }
        if (!is_array($result->return->CampaignAvailableDocumentTypes)) {
            $result->return->CampaignAvailableDocumentTypes = [$result->return->CampaignAvailableDocumentTypes];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->CampaignAvailableDocumentTypes);
            $progress->total($progressCount);
        }
        $cache_storage = [];
        $allRefsCacheList = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_doc_type_ids = [];
            foreach ($result->return->CampaignAvailableDocumentTypes as $I => $campaignAvailableDocumentType) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                if (!isset($campaignAvailableDocumentType->DocumentSets)) {
                    continue;
                }
                if (!is_array($campaignAvailableDocumentType->DocumentSets)) {
                    $campaignAvailableDocumentType->DocumentSets = [$campaignAvailableDocumentType->DocumentSets];
                }
                foreach ($campaignAvailableDocumentType->DocumentSets as $documentSet) {
                    $document_types = $documentSet->DocumentTypes ?? [];
                    if (!is_array($document_types)) {
                        $document_types = [$document_types];
                    }
                    if (empty($document_types)) {
                        continue;
                    }
                    $filters = $documentSet->Filters ?? [];
                    if (!is_array($filters)) {
                        $filters = [$filters];
                    }
                    foreach ($filters as $filter) {
                        $class = null;
                        if (isset($filter->FilterTypeName)) {
                            if ($filter->FilterTypeName == 'ОсобыеОтметки') {
                                $class = AvailableDocumentTypesForConcession::class;
                            } elseif ($filter->FilterTypeName == 'Льготы') {
                                $class = AvailableDocumentTypesForConcession::class;
                            } elseif ($filter->FilterTypeName == 'ИндивидуальныеДостижения') {
                                $class = IndividualAchievementDocumentType::class;
                            }
                        }
                        if ($class === null) {
                            continue;
                        }
                        foreach ($document_types as $document_type) {
                            $data_to_search = (object)[
                                'CampaignRef' => $campaignAvailableDocumentType->CampaignRef,
                                'DocumentSetRef' => $documentSet->DocumentSetRef,
                                'DocumentTypeRef' => $document_type->DocumentTypeRef,
                            ];
                            $local = $class::find()
                                ->where(ArrayHelper::merge(
                                    [
                                        'scan_required' => (bool)$document_type->ScanRequired
                                    ],
                                    $class::getReferenceTypeSearchArray($data_to_search, $cache_storage, $allRefsCacheList)
                                ));
                            if ($class == AvailableDocumentTypesForConcession::class) {
                                $local = $local
                                    ->joinWith(['filterJunctions filter_junctions'])
                                    ->andWhere(['filter_junctions.subject_type' => (string)$filter->FilterTypeName]);
                            }
                            $local = $local
                                ->joinWith(['availableDocumentTypeFilterRef available_document_type_filter_ref'])
                                ->andWhere(['available_document_type_filter_ref.reference_uid' => $filter->FilterValueRef->ReferenceUID]);
                            $local = $local->one();

                            if (!$local) {
                                $local = new $class();
                            }
                            $local->campaign_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($data_to_search, 'CampaignRef', 'ReferenceId', 'IdPK');
                            $local->document_type = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($data_to_search, 'DocumentTypeRef', 'ReferenceId', 'DocumentType');
                            $local->document_set_code = ReferenceTypeManager::GetOneSFieldByRefTypeOrSimpleField($data_to_search, 'DocumentSetRef', 'ReferenceId', 'DocumentSetCode');
                            $local->scan_required = (bool)$document_type->ScanRequired || (bool)$documentSet->NeedOneOfDocuments;
                            $local->need_one_of_documents = (bool)$documentSet->NeedOneOfDocuments;

                            $local->archive = false;
                            if ($local instanceof IndividualAchievementDocumentType) {
                                $local->from1c = true;
                                $local->scenario = $class::$SCENARIO_WITHOUT_EXISTS_CHECK;
                            }
                            $local->loadRefKeysWithCaching($data_to_search, true, $cache_storage);

                            if (!$local->save()) {
                                throw new RecordNotValid($local);
                            }
                            if (!isset($touched_doc_type_ids[$class])) {
                                $touched_doc_type_ids[$class] = [];
                            }
                            $touched_doc_type_ids[$class][] = $local->id;
                            $local_filter = ReferenceTypeManager::GetOrCreateReference(StoredAvailableDocumentTypeFilterReferenceType::class, $filter->FilterValueRef);
                            $local->link(
                                'availableDocumentTypeFilterRef',
                                $local_filter,
                                ($local instanceof AvailableDocumentTypesForConcession) ? ['subject_type' => (string)$filter->FilterTypeName] : []

                            );
                        }
                    }
                }
            }

            AvailableDocumentTypesForConcession::updateAll(['archive' => true], ['not', ['id' => $touched_doc_type_ids[AvailableDocumentTypesForConcession::class] ?? []]]);
            IndividualAchievementDocumentType::updateAll(['archive' => true], [
                'and',
                ['from1c' => true],
                ['not', ['id' => $touched_doc_type_ids[IndividualAchievementDocumentType::class] ?? []]]
            ]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadReasonsForExam(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetReasons', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            Yii::error("Ошибка при выполнении метода GetReasons : {$result->return->UniversalResponse->Description} " . PHP_EOL . print_r($result, true));
            $exceptionFrom1C = new soapException('Ошибка обновления из 1с', '110022', 'GetReasons', $result->return->UniversalResponse->Description);
            return [-1, $exceptionFrom1C];
        }

        if ($result === false || !isset($result->return->Reason)) {
            return [0, []];
        }

        if (!is_array($result->return->Reason)) {
            $result->return->Reason = [$result->return->Reason];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->Reason);
            $progress->total($progressCount);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_reason_ids = [];
            foreach ($result->return->Reason as $I => $reason) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $local_reason = DictionaryReasonForExam::findOne(
                    [
                        'code' => (string)$reason->ReasonCode,
                    ]
                );
                if (is_null($local_reason)) {
                    $local_reason = new DictionaryReasonForExam();
                }
                $local_reason->code = (string)$reason->ReasonCode;
                $local_reason->name = (string)$reason->ReasonName;
                $local_reason->archive = false;

                if ($local_reason->validate()) {
                    $local_reason->save(false);
                    $touched_reason_ids[] = $local_reason->id;
                } else {
                    throw new RecordNotValid($local_reason);
                }
            }
            DictionaryReasonForExam::updateAll(['archive' => true], ['not', ['id' => $touched_reason_ids]]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadFamilyTypes(?Progress $progress = null)
    {
        try {
            $respones = GetReferencesManager::getReferences(FamilyType::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        $references = $respones->getReferences();

        if (empty($references)) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $references = iterator_to_array($references);
            $progressCount = count($references);
            $progress->total($progressCount);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_family_type_ids = [];
            foreach ($references as $I => $reference) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $touched_family_type_ids[] = ReferenceTypeManager::GetOrCreateReference(FamilyType::class, $reference)->id;
            }
            FamilyType::updateAll(['archive' => true], ['not', ['id' => $touched_family_type_ids]]);

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadSpecialRequirements(?Progress $progress = null)
    {
        try {
            $response = GetReferencesManager::getReferences(SpecialRequirementReferenceType::getReferenceClassName());
        } catch (Throwable $e) {
            return [-1, $e];
        }

        if (empty($response->getReferences())) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = iterator_count($response->getReferences());
            $progress->total($progressCount);
        }


        $transaction = Yii::$app->db->beginTransaction();
        try {
            $touched_record_ids = [];
            foreach ($response->getReferences() as $I => $reference) {
                if ($progress) {
                    $progress->current($I + 1);
                }
                $touched_record_ids[] = ReferenceTypeManager::GetOrCreateReference(SpecialRequirementReferenceType::class, $reference)->id;
            }
            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }

            SpecialRequirementReferenceType::updateAll(['archive' => true], ['not', ['id' => $touched_record_ids]]);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadGetEducationLevelDocumentTypeMap(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetEducationLevelDocumentTypeMap', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (\Throwable $e) {
            return [-1, $e];
        }
        if ($result === false) {
            return [0, []];
        }
        if (!isset($result->return->EducationLevelDocumentTypeMap)) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->EducationLevelDocumentTypeMap);
            $progress->total($progressCount);
        }
        $cache_storage = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            EducationDataFilter::deleteAll();

            if (!is_array($result->return->EducationLevelDocumentTypeMap)) {
                $result->return->EducationLevelDocumentTypeMap = [$result->return->EducationLevelDocumentTypeMap];
            }
            foreach ($result->return->EducationLevelDocumentTypeMap as $I => $record) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $tmp = new EducationDataFilter();
                $tmp->loadRefKeysWithCaching($record, true, $cache_storage);
                $tmp->period = $record->Period ?? null;
                $tmp->actual = $record->Actual ?? null;
                $tmp->allow_profile_input = $record->AllowProfileInput ?? false;
                if (!$tmp->save()) {
                    throw new RecordNotValid($tmp);
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadGetAdditionalReceiptDateControl(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetAdditionalReceiptDateControl', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
        } catch (\Throwable $e) {
            return [-1, $e];
        }
        if ($result === false) {
            return [0, []];
        }
        if (!isset($result->return->AdditionalReceiptDateControl)) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->AdditionalReceiptDateControl);
            $progress->total($progressCount);
        }
        $cache_storage = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            AdditionalReceiptDateControl::deleteAll();

            if (!is_array($result->return->AdditionalReceiptDateControl)) {
                $result->return->AdditionalReceiptDateControl = [$result->return->AdditionalReceiptDateControl];
            }
            foreach ($result->return->AdditionalReceiptDateControl as $I => $record) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $tmp = new AdditionalReceiptDateControl();
                $tmp->loadRefKeysWithCaching($record, true, $cache_storage);
                $tmp->stage = $record->Stage ?? null;
                $tmp->date_start = $record->DateStart ?? null;
                $tmp->date_end = $record->DateFinal ?? null;
                $tmp->scenario = AdditionalReceiptDateControl::$SCENARIO_WITHOUT_EXISTS_CHECK;
                if (!$tmp->save()) {
                    throw new RecordNotValid($tmp);
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadDocumentTypePropertySettings(?Progress $progress = null)
    {
        try {
            $result = Yii::$app->soapClientAbit->load('GetDocumentTypePropertySettings', [], DebuggingSoap::getInstance()->enable_logging_for_dictionary_soap);
        } catch (\Throwable $e) {
            return [-1, $e];
        }
        if ($result === false) {
            return [0, []];
        }
        if (!isset($result->return->DocumentTypeProperties)) {
            return [0, []];
        }
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($result->return->DocumentTypeProperties);
            $progress->total($progressCount);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!is_array($result->return->DocumentTypeProperties)) {
                $result->return->DocumentTypeProperties = [$result->return->DocumentTypeProperties];
            }
            DocumentTypeAttributeSetting::deleteAll();
            DocumentTypePropertiesSetting::deleteAll();

            foreach ($result->return->DocumentTypeProperties as $I => $record) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                


                $documentType = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $record->DocumentTypeRef);
                $documentTypeSetting = DocumentTypePropertiesSetting::getOrCreateByDocumentType($documentType);
                foreach ($record->Properties as $property) {
                    $documentTypeSetting->setupPropertySetting($property->Name, $property->IsUsed, $property->FillChecking);
                }
            }
            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function linkRefODataFields()
    {
        AdmissionCategory::updateLinks();
        DocumentType::updateLinks();
        Privilege::updateLinks();
        SpecialMark::updateLinks();
        Olympiad::updateLinks();
        EducationType::updateLinks();

        return $this->getSuccessAnswer();
    }

    public function loadDictionaryPredmetOfExamsSchedule(?Progress $progress = null)
    {
        $campaigns = AdmissionCampaign::findAll(['archive' => false]);
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($campaigns);
            $progress->total($progressCount);
        }
        $cacheStorage = [];
        $allRefsCacheList = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            DictionaryPredmetOfExamsSchedule::updateAll(['archive' => true]);

            foreach ($campaigns as $I => $campaign) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }
                try {
                    $refData = ReferenceTypeManager::GetReference($campaign, 'referenceType');
                    $result = Yii::$app->soapClientAbit->load(
                        'GetPredmetsOfExamsSchedule',
                        ['CampaignRef' => $refData],
                        DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled
                    );
                } catch (Throwable $e) {
                    return [-1, $e];
                }
                if ($result === false) {
                    continue;
                }
                if (!isset($result->return->PredmetOfExamsSchedule)) {
                    continue;
                }
                if (!is_array($result->return->PredmetOfExamsSchedule)) {
                    $result->return->PredmetOfExamsSchedule = [$result->return->PredmetOfExamsSchedule];
                }

                foreach ($result->return->PredmetOfExamsSchedule as $schedule) {
                    $predmetOfExamsSchedule = DictionaryPredmetOfExamsSchedule::find()
                        ->andWhere(DictionaryPredmetOfExamsSchedule::getReferenceTypeSearchArray($schedule, $cacheStorage, $allRefsCacheList))
                        ->one();

                    if (!$predmetOfExamsSchedule) {
                        $predmetOfExamsSchedule = new DictionaryPredmetOfExamsSchedule();
                    }
                    $predmetOfExamsSchedule->archive = false;
                    $predmetOfExamsSchedule->predmet_guid = $schedule->PredmetGUID;
                    $predmetOfExamsSchedule->loadRefKeysWithCaching($schedule, true, $cacheStorage);
                    if (!$predmetOfExamsSchedule->save()) {
                        throw new RecordNotValid($predmetOfExamsSchedule);
                    }
                }
            }

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();

            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    public function loadDictionaryDateTimeOfExamsSchedule(?Progress $progress = null)
    {
        $campaigns = AdmissionCampaign::findAll(['archive' => false]);
        $progressCount = 0;
        if ($progress) {
            $progressCount = count($campaigns);
            $progress->total($progressCount);
        }

        $cacheStorage = [];
        $allRefsCacheList = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            DictionaryDateTimeOfExamsSchedule::updateAll(['archive' => true]);

            foreach ($campaigns as $I => $campaign) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }
                try {
                    $refData = ReferenceTypeManager::GetReference($campaign, 'referenceType');
                    $result = Yii::$app->soapClientAbit->load(
                        'GetDateTimeOfExamsSchedule',
                        ['CampaignRef' => $refData],
                        DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled
                    );
                } catch (Throwable $e) {
                    return [-1, $e];
                }
                if ($result === false) {
                    continue;
                }
                if (!isset($result->return->DateTimeOfExamsSchedule)) {
                    continue;
                }
                if (!is_array($result->return->DateTimeOfExamsSchedule)) {
                    $result->return->DateTimeOfExamsSchedule = [$result->return->DateTimeOfExamsSchedule];
                }

                foreach ($result->return->DateTimeOfExamsSchedule as $schedule) {
                    $dateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::find()
                        ->andWhere(DictionaryDateTimeOfExamsSchedule::getReferenceTypeSearchArray($schedule, $cacheStorage, $allRefsCacheList))
                        ->andWhere([
                            'predmet_guid' => $schedule->PredmetGUID,
                            'guid_date_time' => $schedule->GUIDDateTime,
                        ])
                        ->one();

                    if (!$dateTimeOfExamsSchedule) {
                        $dateTimeOfExamsSchedule = new DictionaryDateTimeOfExamsSchedule();
                    }
                    $dateTimeOfExamsSchedule->archive = false;
                    $dateTimeOfExamsSchedule->note = $schedule->Note;
                    $dateTimeOfExamsSchedule->endDate = $schedule->EndDate;
                    $dateTimeOfExamsSchedule->startDate = $schedule->StartDate;
                    $dateTimeOfExamsSchedule->predmet_guid = $schedule->PredmetGUID;
                    $dateTimeOfExamsSchedule->guid_date_time = $schedule->GUIDDateTime;
                    $dateTimeOfExamsSchedule->registrationDate = $schedule->RegistrationDate;

                    $dateTimeOfExamsSchedule->loadRefKeysWithCaching($schedule, true, $cacheStorage);
                    if (!$dateTimeOfExamsSchedule->save()) {
                        throw new RecordNotValid($dateTimeOfExamsSchedule);
                    }
                }
            }

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();

            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    protected function loadOneReferenceDictionary(
        $class,
        Closure $onBeginFilling = null,
        Closure $onNextReference = null,
        Closure $onEndFilling = null,
        ?Progress $progress = null
    ) {
        $updateManager = new AppUpdate();
        try {
            $updateManager->updateReferenceTable($class, $onBeginFilling, $onNextReference, $onEndFilling, $progress);
            return $this->getSuccessAnswer();
        } catch (Throwable $e) {
            return [-1, $e];
        }
    }

    public function loadDocumentTypes(?Progress $progress = null)
    {
        $onEndFilling = function () {
            $doc = DocumentType::findOne([
                'predefined_data_name' => AdmissionAgreement::DOCUMENT_TYPE_PREDEFINED_DATA_NAME,
                'archive' => false
            ]);

            $code_setting = CodeSetting::findOne(['name' => 'agreement_document_type_guid']);
            if ($code_setting) {
                $code_setting->value = $doc->ref_key;
                if (!$code_setting->save(true, ['value'])) {
                    Yii::error('Не удалось обновить код по умолчанию agreement_document_type_guid ' . VarDumper::dumpAsString($code_setting->errors));
                }
            }
        };

        return $this->loadOneReferenceDictionary(DocumentType::class, null, null, $onEndFilling, $progress);
    }

    public function loadPrivileges(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(Privilege::class, null, null, null, $progress);
    }

    public function loadAdmissionBase(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(AdmissionBase::class, null, null, null, $progress);
    }

    public function loadBudgetLevel(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(BudgetLevel::class, null, null, null, $progress);
    }

    public function loadAdmissionCategory(?Progress $progress = null)
    {
        $onEndFilling = function () {
            $cat_all = CodeSetting::find()->where([
                'name' => 'category_all',
                'description' => 'Код категории приема на общих основаниях'
            ])->one();
            if ($cat_all == null) {
                $cat_all = new CodeSetting();
                $cat_all->attributes = [
                    'name' => 'category_all',
                    'description' => 'Код категории приема на общих основаниях'
                ];
            }
            $admission_category = AdmissionCategory::find()->active()->andWhere(['description' => 'На общих основаниях'])->one();
            $cat_all->value = ArrayHelper::getValue($admission_category, 'ref_key', '');
            $cat_all->save();

            $cat_lgota = CodeSetting::find()
                ->where([
                    'name' => 'category_specific_law',
                    'description' => 'Код категории приема поступающих имеющих особое право'
                ])
                ->one();
            if ($cat_lgota == null) {
                $cat_lgota = new CodeSetting();
                $cat_lgota->attributes = [
                    'name' => 'category_specific_law',
                    'description' => 'Код категории приема поступающих имеющих особое право'
                ];
            }
            $admission_category = AdmissionCategory::find()->active()->andWhere(['description' => 'Имеющие особое право'])->one();
            $cat_lgota->value = ArrayHelper::getValue($admission_category, 'ref_key', '');
            $cat_lgota->save();
        };

        $onNextRef = function ($index, $countReferences, $reference) {
            $predefined_name = $reference->PredefinedDataName ?? '';
            
            return !in_array((string)$predefined_name, ['БезВступительныхИспытаний', 'УдалитьБезВступительныхИспытаний']);
        };

        return $this->loadOneReferenceDictionary(AdmissionCategory::class, null, $onNextRef, $onEndFilling, $progress);
    }

    public function loadDocumentShipment(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(DocumentShipment::class, null, null, null, $progress);
    }

    public function loadCountry(?Progress $progress = null)
    {
        $onNextRef = function ($index, $countReferences, &$reference) {
            $reference->ReferenceName = mb_convert_case(mb_strtolower((string)$reference->ReferenceName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            return true;
        };

        return $this->loadOneReferenceDictionary(Country::class, null, $onNextRef, null, $progress);
    }

    public function loadOwnageForms(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(OwnageForm::class, null, null, null, $progress);
    }

    public function loadStoredProfileReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredProfileReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredEducationLevelReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredEducationLevelReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredEducationFormReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredEducationFormReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredDisciplineReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredDisciplineReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredDisciplineFormReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredDisciplineFormReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredEducationSourceReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredEducationSourceReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredDocumentSetReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredDocumentSetReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredVariantOfRetestReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredVariantOfRetestReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredDocumentCheckStatusReferenceType(?Progress $progress = null)
    {
        return $this->loadOneReferenceDictionary(StoredDocumentCheckStatusReferenceType::class, null, null, null, $progress);
    }

    public function loadStoredCompetitiveGroupReferenceType(?Progress $progress = null)
    {
        $admissionCampaign = AdmissionCampaign::find()->active()->each();
        $progressCount = 0;
        if ($progress) {
            $admissionCampaign = iterator_to_array($admissionCampaign);
            $progressCount = count($admissionCampaign);
            $progress->total($progressCount);
        }
        try {
            $touched_ids = [];
            foreach ($admissionCampaign as $I => $campaign) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $campaignReferenceType = ArrayHelper::getValue($campaign, 'referenceType');
                $result = GetReferencesManager::getReferences(
                    StoredCompetitiveGroupReferenceType::getReferenceClassToFill(),
                    '',
                    GetReferencesManager::FILTER_TYPE_AP,
                    [
                        'SimpleFilters' => [
                            [
                                'Field' => 'ПриемнаяКампания',
                                'Comparison' => 'Equal',
                                'Values' => [
                                    'ValueType' => StoredAdmissionCampaignReferenceType::getReferenceClassName(),
                                    'ValueRef' => ReferenceTypeManager::getReference($campaignReferenceType),
                                ]
                            ]
                        ]
                    ]
                );
                $references = $result->getReferences();
                foreach ($references as $index => $reference) {
                    

                    $storedReference = ReferenceTypeManager::GetOrCreateReference(StoredCompetitiveGroupReferenceType::class, $reference);
                    $storedReference->fillDictionary();
                    $campaignReferenceType->link('competitiveGroups', $storedReference);
                    $touched_ids[] = $storedReference->id;
                }
            }
            StoredCompetitiveGroupReferenceType::updateAll(
                [StoredCompetitiveGroupReferenceType::getArchiveColumnName() => StoredCompetitiveGroupReferenceType::getArchiveColumnPositiveValue()],
                ['not', ['id' => $touched_ids]]
            );
            foreach ($touched_ids as $touched_id) {
                $record = StoredCompetitiveGroupReferenceType::findOne($touched_id);
                $record->restoreDictionary();
            }

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (Throwable $e) {
            return [-1, $e];
        }
        return $this->getSuccessAnswer();
    }

    public function loadContractorList(?Progress $progress = null)
    {
        $contractors = GetContractorListManager::getReferences(StoredContractorReferenceType::getReferenceClassName());
        $progressCount = 0;
        if ($progress) {
            $progressCount = iterator_count($contractors->getReferences());
            $progress->total($progressCount);
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            Contractor::updateAll(['archive' => true]);

            foreach ($contractors->getReferences() as $I => $contractor) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $raw_contractor = ToAssocCaster::getAssoc($contractor);
                $model = ContractorManager::GetOrCreateContractor($raw_contractor);

                if ($model === null) {
                    Yii::error(
                        'Не удалось добавить/обновить контрагента: ' . VarDumper::dumpAsString($contractor),
                        'loadContractorList'
                    );
                    continue;
                }
            }

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return [-1, $e];
        }

        return $this->getSuccessAnswer();
    }

    private function getDictionaryBatchSize(): int
    {
        
        
        $batchSize = getenv('DICTIONARY_REQUEST_BATCH_SIZE');
        if (!$batchSize || !is_numeric($batchSize)) {
            $batchSize = 300;
        }
        return (int)$batchSize;
    }
}
