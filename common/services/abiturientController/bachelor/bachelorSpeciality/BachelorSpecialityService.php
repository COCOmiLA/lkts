<?php

namespace common\services\abiturientController\bachelor\bachelorSpeciality;

use Closure;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\RegulationRelationManager;
use common\components\soapClientManager;
use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\EmptyCheck;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\repositories\BachelorSpecialityRepository;
use common\modules\abiturient\models\repositories\SpecialityRepository;
use common\services\abiturientController\bachelor\BachelorService;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

class BachelorSpecialityService extends BachelorService
{
    public const BUILD_APPLICATION_TYPE_FULL = 'full';
    public const BUILD_APPLICATION_TYPE_BUDGET = 'budget';
    public const BUILD_APPLICATION_TYPE_COMMERCIAL = 'commercial';
    public const BUILD_APPLICATION_TYPE_ENLISTED_ONLY = 'enlisted';

    




    public function getRegulationsAndAttachmentsForEducation(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_APPLICATION,
            RegulationRelationManager::RELATED_ENTITY_APPLICATION
        );
    }

    




    public function getSelectedSpecialityList(BachelorApplication $application): array
    {
        return $application->getSpecialities()
            ->with('speciality')
            ->with('speciality.subdivisionRef')
            ->with('speciality.competitiveGroupRef')
            ->with('speciality.educationFormRef')
            ->with('speciality.parentCombinedCompetitiveGroupRef')
            ->indexBy('id')
            ->all();
    }

    




    public function getAvailableSpecialityList(BachelorApplication $application): array
    {
        $allowBenefitCategories = !ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
        return SpecialityRepository::getCurrentAvailableSpecialities(
            $application,
            $allowBenefitCategories
        )
            ->with('detailGroupRef')
            ->with('subdivisionRef')
            ->with('educationFormRef')
            ->with('educationFormRef')
            ->with('educationSourceRef')
            ->with('competitiveGroupRef')
            ->all();
    }

    








    public function checkUpdateContractDocsFrom1C(BachelorApplication $application, array $bachelorSpecialityialties): void
    {
        $filteredSpecialities = array_filter(
            $bachelorSpecialityialties,
            function (BachelorSpeciality $bachelorSpeciality) {
                return $bachelorSpeciality->isFullCostRecovery();
            }
        );

        if ($filteredSpecialities) {
            $application->updateContractDocsFrom1C();
        }
    }

    






    public function validateAllSpecialities(array $bachelorSpecialityialties): void
    {
        foreach ($bachelorSpecialityialties as $bachelorSpecialityiality) {
            $bachelorSpecialityiality->scenario = BachelorSpeciality::SCENARIO_FULL_VALIDATION;
            $bachelorSpecialityiality->validate();
        }
    }

    











    public function processLoadedData(
        BachelorApplication $application,
        array               $bachelorSpecialityialties,
        array               $dataFromPost,
        bool                $requiredResetStatus
    ): array {
        if ($application->type->rawCampaign->common_education_document) {
            $application->load(Yii::$app->request->post());
        }
        ActiveRecord::loadMultiple($bachelorSpecialityialties, $dataFromPost);

        $hasErrors = false;
        $hasChanges = false;

        $bachelorSpecialityIndex = 0;
        foreach ($bachelorSpecialityialties as $bachelorSpeciality) {
            

            if ($bachelorSpeciality->is_enlisted) {
                continue;
            }

            [$hasChanges, $hasErrors] = $this->saveProcessBachelorSpeciality(
                $application,
                $bachelorSpecialityialties,
                $bachelorSpeciality,
                $bachelorSpecialityIndex,
                $hasErrors,
                $hasChanges,
                $requiredResetStatus
            );

            $bachelorSpecialityIndex++;
        }

        return [$hasChanges, $hasErrors];
    }

    





    public function getNextStep(BachelorApplication $application, string $currentStep = 'specialities'): string
    {
        return parent::getNextStep($application, $currentStep);
    }

    










    public function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array {
        if (
            $application->canEdit() ||
            $application->hasPassedApplicationWithEditableAttachments(AttachmentType::RELATED_ENTITY_APPLICATION)
        ) {
            return parent::postProcessingRegulationsAndAttachments($application, $attachments, $regulations);
        }

        return [
            'hasChanges' => false,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    







    public function getFileApplicationReturnForm(
        User                $user,
        BachelorApplication $application,
        string              $reportType,
        soapClientManager   $soapClientManager
    ): array {
        $applicationTypeName = ArrayHelper::getValue($application, 'type.name');
        $typeName = Yii::t(
            'abiturient/bachelor/print-application-return-form/all',
            'Имя файла бланка заявления на отзыв: `Заявление на отзыв {APPLICATION_TYPE_NAME}`',
            ['APPLICATION_TYPE_NAME' => $applicationTypeName]
        );
        if ($reportType === 'ApplicationReturn') {
            $typeName = Yii::t(
                'abiturient/bachelor/print-application-return-form/all',
                'Имя файла бланка отзыва согласия: `Отзыв согласия {APPLICATION_TYPE_NAME}`',
                ['APPLICATION_TYPE_NAME' => $applicationTypeName]
            );
        }

        return $this->getFileForm1C(
            $user,
            $application,
            $reportType,
            $soapClientManager,
            'GetDocumentsReturnForm',
            $typeName
        );
    }

    public function getFileEnrollmentRejectionForm(
        User $user, 
        BachelorApplication $application,
        soapClientManager $soapClientManager,
        int $bachelor_spec_id
    ): array
    {
        $typeName = Yii::t(
            'abiturient/bachelor/print-application-return-form/all',
            'Имя файла бланка отказа от зачисления: `Отказ от зачисления`'
        );

        return $this->getFileForm1C(
            $user,
            $application,
            'enrollment_rejection',
            $soapClientManager,
            'GetApplicationReport',
            $typeName,
            [],
            BachelorSpecialityService::BUILD_APPLICATION_TYPE_ENLISTED_ONLY,
            $this->getSpecialitiesFiltrationCallbackForEnlisted($bachelor_spec_id)
        );
    }

    









    public function getFileApplicationReport(
        User                        $user,
        BachelorApplication         $application,
        string                      $reportType,
        soapClientManager           $soapClientManager,
        SpecialityPrioritiesService $specialityPrioritiesService,
        string                      $applicationBuildType = BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL
    ): array {
        return $this->getFileForm1C(
            $user,
            $application,
            $reportType,
            $soapClientManager,
            'GetApplicationReport',
            Yii::t(
                'abiturient/bachelor/print-application/all',
                'Имя файла с заявлением на странице печатной формы: `заявление в приемную комиссию {APPLICATION_TYPE_NAME}`',
                ['APPLICATION_TYPE_NAME' => ArrayHelper::getValue($application, 'type.name')]
            ),
            $this->educationSourceReferenceUidListByBuildType(
                $specialityPrioritiesService,
                $applicationBuildType
            ),
            $applicationBuildType
        );
    }

    





    private function educationSourceReferenceUidListByBuildType(
        SpecialityPrioritiesService $specialityPrioritiesService,
        string                      $applicationBuildType = BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL
    ): array {
        switch ($applicationBuildType) {
            case BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL:
                return [];

            case BachelorSpecialityService::BUILD_APPLICATION_TYPE_BUDGET:
                return $specialityPrioritiesService->getFinancialBasisFilterForBudget();

            case BachelorSpecialityService::BUILD_APPLICATION_TYPE_COMMERCIAL:
                return $specialityPrioritiesService->getFinancialBasisFilterForCommercial();
                
            case BachelorSpecialityService::BUILD_APPLICATION_TYPE_ENLISTED_ONLY:
                return [];
        }

        throw new UserException("Указан некорректный тип сборки заявления: {$applicationBuildType}");
    }

    




    public function checkCanRemoveSpeciality(?BachelorSpeciality $bachelorSpeciality): array
    {
        if (!$bachelorSpeciality) {
            return [
                'hasError' => true,
                'errorMessage' => Yii::t(
                    'abiturient/bachelor/application/remove-speciality',
                    'Текст ошибки при попытке удалить НП которое не существует; на страницы НП: `Не удалось найти выбранное направление подготовки`'
                )
            ];
        }

        $checkAttrsBeforeDelete = [
            'is_enlisted' => Yii::t(
                'abiturient/bachelor/application/remove-speciality',
                'Текст ошибки при попытке удалить НП которое было одобрено в 1С; на страницы НП: `Невозможно удалить направление подготовки, так оно принято в принято в приёмную комиссию.`'
            ),
            'agreement' => Yii::t(
                'abiturient/bachelor/application/remove-speciality',
                'Текст ошибки при попытке удалить НП когда к нему прикреплено согласие на зачисление; на страницы НП: `Невозможно удалить направление подготовки, так как к нему прикреплено согласие на зачисление.`'
            ),
            'agreementDecline' => Yii::t(
                'abiturient/bachelor/application/remove-speciality',
                'Текст ошибки при попытке удалить НП когда к нему прикреплен отказ на согласие на зачисление; на страницы НП: `Невозможно удалить направление подготовки, так как к нему прикреплен неподтвержденный отзыв согласия на зачисление.`'
            ),
        ];
        foreach ($checkAttrsBeforeDelete as $attr => $errorMessage) {
            if ($bachelorSpeciality->{$attr}) {
                return [
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ];
            }
        }

        if ($bachelorSpeciality->speciality && $bachelorSpeciality->speciality->parentCombinedCompetitiveGroupRef) {
            $parent_speciality_ids = $bachelorSpeciality->speciality->getParentCombinedCompetitiveGroupRefSpeciality()->select(['id'])->column();
            if ($bachelorSpeciality->application->getSpecialities()->andWhere(['speciality_id' => $parent_speciality_ids])->exists()) {
                return [
                    'hasError' => true,
                    'errorMessage' => Yii::t(
                        'abiturient/bachelor/application/remove-speciality',
                        'Текст ошибки при попытке удалить НП когда для него выбрана конкурсная группа совмещённой квоты; на страницы НП: `Невозможно удалить направление подготовки, сначала удалите корневое направление подготовки.`'
                    )
                ];
            }
        }

        return [
            'hasError' => false,
            'errorMessage' => '',
        ];
    }

    




    public function getBachelorSpecialityFromPostByApplication(BachelorApplication $application): ?BachelorSpeciality
    {
        $tn = BachelorSpeciality::tableName();
        return $application->getSpecialitiesWithoutOrdering()
            ->andWhere(["{$tn}.id" => (int)$this->request->post('id')])
            ->one();
    }

    














    private function getFileForm1C(
        User                $user,
        BachelorApplication $application,
        string              $reportType,
        soapClientManager   $soapClientManager,
        string              $fileForm1CServiceName,
        string              $typeName,
        array               $educationSourceReferenceUidList = [],
        string              $applicationBuildType = BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL,
        ?Closure            $customSpecialitiesFilterCallback = null
    ): array {

        if ($customSpecialitiesFilterCallback !== null) {
            $spec_filter = $customSpecialitiesFilterCallback;
        } else {
            $spec_filter = $this->getSpecialitiesFiltrationCallbackByBuildType(
                $educationSourceReferenceUidList
            );
        }

        $package = (new FullApplicationPackageBuilder($application))
            ->setSpecialitiesFiltrationCallback($spec_filter)
            ->build();

        $request = [
            'EntrantPackage' => $package,
            'ReportType' => $reportType
        ];
        $result = $soapClientManager->load($fileForm1CServiceName, $request);
        if ($result === false) {
            throw new ServerErrorHttpException('Не удалось подключиться к серверу 1С');
        }
        $response = $result->return->UniversalResponse;
        if (!(bool)$response->Complete) {
            throw new ServerErrorHttpException($response->Description);
        }

        $file = $result->return->Scan;
        $userName = $user->userProfile->getFullName();
        $fileName = "$userName, $typeName";
        if ($applicationBuildType == BachelorSpecialityService::BUILD_APPLICATION_TYPE_BUDGET) {
            $fileName .= Yii::t(
                'abiturient/bachelor/application/application-block',
                'Примечание типа заявления при генерации печатного заявления; на странице НП: ` (бюджетная основа)`'
            );
        } elseif ($applicationBuildType == BachelorSpecialityService::BUILD_APPLICATION_TYPE_COMMERCIAL) {
            $fileName .= Yii::t(
                'abiturient/bachelor/application/application-block',
                'Примечание типа заявления при генерации печатного заявления; на странице НП: ` (платная основа)`'
            );
        }

        return [
            'base64FileBinaryCode' => base64_decode($file->FileBinaryCode),
            'fullFileName' => "$fileName.pdf",
        ];
    }

    



    public function makeSpecialitiesListHierarchical(array $bachelorSpecialities): array
    {
        $result = [];
        foreach ($bachelorSpecialities as $index => $bachelorSpeciality) {
            $speciality = $bachelorSpeciality->speciality;
            if ($speciality->is_combined_competitive_group && $speciality->competitiveGroupRef) {
                
                $childSpecialities = $this->findSpecialitiesWithParentCombinedCompetitiveGroupRef($bachelorSpecialities, $speciality);
                $result[$index] = [$bachelorSpeciality, $childSpecialities];
            } else if ($speciality->parentCombinedCompetitiveGroupRef) {
                
                $parentSpeciality = $this->findSpecialityWithCompetitiveGroupRef($bachelorSpecialities, $speciality);
                if (!$parentSpeciality) {
                    $result[$index] = $bachelorSpeciality;
                }
                

            } else {
                $result[$index] = $bachelorSpeciality;
            }
        }
        return $result;
    }

    public function flattenSpecialities(array $bachelorSpecialities): array
    {
        $return = [];
        array_walk_recursive($bachelorSpecialities, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

    private function findSpecialitiesWithParentCombinedCompetitiveGroupRef(array $bachelorSpecialities, Speciality $speciality): array
    {
        $competitiveGroupReferenceType = $speciality->competitiveGroupRef;
        $result = [];
        foreach ($bachelorSpecialities as $index => $bachelorSpeciality) {
            $inner_speciality = $bachelorSpeciality->speciality;
            if (
                $speciality->id != $inner_speciality->id
                && $inner_speciality->parentCombinedCompetitiveGroupRef
                && $inner_speciality->parentCombinedCompetitiveGroupRef->reference_uid == $competitiveGroupReferenceType->reference_uid
            ) {
                $result[$index] = $bachelorSpeciality;
            }
        }
        return $result;
    }

    private function findSpecialityWithCompetitiveGroupRef(array $bachelorSpecialities, Speciality $speciality): ?BachelorSpeciality
    {
        $competitiveGroupReferenceType = $speciality->parentCombinedCompetitiveGroupRef;

        foreach ($bachelorSpecialities as $bachelorSpeciality) {
            $inner_speciality = $bachelorSpeciality->speciality;
            if (
                $speciality->id != $inner_speciality->id
                && $inner_speciality->competitiveGroupRef
                && $inner_speciality->competitiveGroupRef->reference_uid == $competitiveGroupReferenceType->reference_uid
            ) {
                return $bachelorSpeciality;
            }
        }
        return null;
    }

    









    private function saveProcessBachelorSpeciality(
        BachelorApplication $application,
        array               $bachelorSpecialityialties,
        BachelorSpeciality  $bachelorSpeciality,
        int                 $bachelorSpecialityIndex,
        bool                $hasErrors,
        bool                $hasChanges,
        bool                $requiredResetStatus
    ): array {
        $bachelorSpeciality->setScenario(BachelorSpeciality::SCENARIO_FULL_VALIDATION);

        if (ArrayHelper::getValue($bachelorSpeciality, 'speciality.educationSourceRef.reference_uid') === $this->configurationManager->getCode('target_reception_guid')) {
            $categoryAll = AdmissionCategory::findByUID($this->configurationManager->getCode('category_all'));
            $bachelorSpeciality->admission_category_id = $categoryAll->id;
        }

        $prevSpecsWithSameDirection = $this->buildPrivilegesSpecsWithSameDirectionList(
            $bachelorSpecialityialties,
            $bachelorSpeciality,
            $bachelorSpecialityIndex
        );

        if (!empty($prevSpecsWithSameDirection)) {
            $bachelorSpeciality->target_reception_id = null; 
            $bachelorSpeciality->addError('target_reception_id', 'Это целевое направление уже используется');
            $hasErrors = true;
        } else {
            if ($bachelorSpeciality->hasChangedAttributes()) {
                $hasChanges = true;
                if ($bachelorSpeciality->save()) {
                    if ($requiredResetStatus) {
                        $application->resetStatus();
                    }
                } else {
                    $hasErrors = true;
                }
            }
        }

        return [$hasChanges, $hasErrors];
    }

    









    private function buildPrivilegesSpecsWithSameDirectionList(array $bachelorSpecialityialties, BachelorSpeciality $bachelorSpeciality, int $bachelorSpecialityIndex): array
    {
        return array_values(
            array_filter(
                array_slice($bachelorSpecialityialties, 0, $bachelorSpecialityIndex) ?? [],
                function ($item) use ($bachelorSpeciality) {
                    if (
                        $item->id != $bachelorSpeciality->id &&
                        !EmptyCheck::isEmpty($item->target_reception_id) &&
                        $item->speciality_id == $bachelorSpeciality->speciality_id &&
                        $item->target_reception_id == $bachelorSpeciality->target_reception_id
                    ) {
                        return true;
                    }
                    return false;
                }
            )
        );
    }

    




    public function getSpecialitiesFiltrationCallbackByBuildType(
        array $educationSourceReferenceUidList = []
    ): ?Closure {
        if (!$educationSourceReferenceUidList) {
            return null;
        }

        return function (ActiveQuery $baseFilterQuery) use ($educationSourceReferenceUidList): ActiveQuery {
            $tnEducationSourceRef = StoredEducationSourceReferenceType::tableName();

            return $baseFilterQuery
                ->joinWith('speciality.educationSourceRef')
                ->andWhere([
                    'IN',
                    "{$tnEducationSourceRef}.reference_uid",
                    $educationSourceReferenceUidList
                ]);
        };
    }

    public function getSpecialitiesFiltrationCallbackForEnlisted(int $bachelor_spec_id): Closure
    {
        return function (ActiveQuery $baseFilterQuery) use ($bachelor_spec_id): ActiveQuery {
            $main_table_name = BachelorSpeciality::tableName();
            return $baseFilterQuery->andWhere([
                $main_table_name . '.id' => $bachelor_spec_id, 
                $main_table_name . '.is_enlisted' => true
            ]);
        };
    }
}
