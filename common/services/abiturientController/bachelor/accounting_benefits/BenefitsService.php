<?php

namespace common\services\abiturientController\bachelor\accounting_benefits;

use common\components\configurationManager;
use common\components\RegulationRelationManager;
use common\models\AttachmentType;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\AdmissionProcedure;
use common\models\dictionary\DocumentType;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\EmptyCheck;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use yii\base\UserException;
use yii\caching\CacheInterface;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class BenefitsService extends AccountingBenefitsService
{
    




    public function getPreferenceListForSelect(int $applicationId): array
    {
        $output = [];
        $params = $this->request->post('depdrop_parents');

        if (!EmptyCheck::isLoadingStringOrEmpty($params[0])) {
            $admissionCategoryId = $params[0];

            $admissionCategory = AdmissionCategory::find()
                ->active()
                ->andWhere(['id' => $admissionCategoryId])
                ->limit(1)
                ->one();
            if ($admissionCategory && $admissionCategory->ref_key == $this->configurationManager->getCode('category_specific_law')) {
                $application = $this->getApplication($applicationId);
                $items = $application->bachelorPreferencesSpecialRight;

                $output = $this->makeDataFormattedForDepDrop(
                    function ($item) {
                        return [
                            'id' => ArrayHelper::getValue($item, 'id'),
                            'name' => ArrayHelper::getValue($item, 'name'),
                        ];
                    },
                    $items
                );
            }
        }

        return array_values(ArrayHelper::index($output, 'id'));
    }

    




    public function __construct(
        Request $request,
        CacheInterface $cache,
        configurationManager $configurationManager
    ) {
        parent::__construct($request, $cache, $configurationManager);
    }

    




    public function getDocTypeDataForSelect(?int $app_id = null): array
    {
        $output = [];
        $selected = '';
        $application = $this->getApplication($app_id);

        if ($this->request->post('depdrop_parents')) {
            $params = $this->request->post('depdrop_parents');
            if (!EmptyCheck::isLoadingStringOrEmpty($params[0])) {
                $pref = BachelorPreferences::getBenefitByHashKey($params[0]);
                $col_name = 'special_mark_id';
                if ($pref instanceof Privilege) {
                    $col_name = 'privilege_id';
                }
                $benefit = BachelorPreferences::findOne(['id_application' => $app_id, $col_name => $pref->id]);
                if (isset($benefit)) {
                    $selected = $benefit->document_type_id;
                }

                $output = $this->makeDataFormattedForDepDrop(
                    function ($item) {
                        return [
                            'id' => $item['maxid'],
                            'name' => $item['description']
                        ];
                    },
                    $this->getDocTypesByBachelorPreferenceCodeAndBachelorApplication($application, $pref)
                );

                if ($benefit && $benefit->document_type_id && !array_filter($output, function ($item) use ($benefit) {
                    return $item['id'] == $benefit->document_type_id;
                })) {
                    $output[] = [
                        'id' => $benefit->document_type_id,
                        'name' => DocumentType::find()
                            ->andWhere(['id' => $benefit->document_type_id])
                            ->select('description')
                            ->scalar()
                    ];
                }
            }
        }

        return ['output' => $output, 'selected' => $selected];
    }

    








    public function getBenefits($id)
    {
        
        $application = $this->getApplication($id);

        
        $benefits = $application
            ->getBachelorPreferencesSpecialRight()
            ->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $benefits,
            'pagination' => ['pageSize' => 10]
        ]);

        return [
            'id' => $id,
            'model' => $this->initBenefit($application),
            'items' => $this->loadBenefitsLists($application),
            'canEdit' => ($application->canEdit() && $application->canEditSpecialities()),
            'itemsDoc' => $this->getDocumentItems(),
            'providers' => $benefits,
            'dataProvider' => $dataProvider,
            'action' => '/site/accounting-benefits'
        ];
    }

    




    public function getRegulationsAndAttachmentsForPreference(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_PREFERENCE,
            RegulationRelationManager::RELATED_ENTITY_PREFERENCE
        );
    }

    








    public function editBenefits(User $currentUser): array
    {
        $formName = (new BachelorPreferences)->formName();
        [
            'id' => $id,
            'appId' => $appId,
            'application' => $application,
        ] = $this->editAccountingBenefits($formName);

        $accountingBenefits = $this->getAccountingBenefitsQueryForEditFunction(
            $id,
            $appId,
            BachelorPreferences::class
        )
            ->notInEnlistedApp()
            ->one();

        if (isset($accountingBenefits)) {
            return $this->updateBenefitFromPost(
                $currentUser,
                $application,
                $accountingBenefits
            );
        }

        return [null, false, false];
    }

    







    public function saveNewBenefits(User $currentUser, ?int $id): array
    {
        $formName = (new BachelorPreferences)->formName();
        $application = $this->saveNewAccountingBenefits($id, $formName);
        $newAccountingBenefits = $this->initBenefit($application);

        return $this->updateBenefitFromPost(
            $currentUser,
            $application,
            $newAccountingBenefits
        );
    }

    








    public function downloadBenefits(User $currentUser, ?int $id): array
    {
        return $this->generateFilesToDownloadAccountingBenefits(
            $currentUser,
            $id,
            BachelorPreferences::class,
            function ($benefit, $_) {
                return "{$benefit->getDescription()} ({$benefit->document_series} {$benefit->document_number}).zip";
            },
            'Не удалось найти льготу или преимущественное право.'
        );
    }

    






    public function canDownloadBenefits(?int $id = null): bool
    {
        return $this->canGenerateFilesToDownloadAccountingBenefits($id, BachelorPreferences::class);
    }

    






    public function updateBenefitFromPost(
        User $currentUser,
        BachelorApplication $application,
        BachelorPreferences $model
    ): array {
        return $this->updateAccountingBenefitsFromPost(
            $currentUser,
            $application,
            $model,
            function ($model) {
                $model->olympiad_code = null;
                $model->olympiad_id = null;
                
                $model->document_type = ArrayHelper::getValue($model, 'documentType.code');

                $model->special_mark_code = ArrayHelper::getValue($model, 'specialMark.code');
                $model->privilege_code = ArrayHelper::getValue($model, 'privilege.code');
                $model->document_type = ArrayHelper::getValue($model, 'documentType.code');

                return $model;
            },
            'Ошибка при редактировании льгот:'
        );
    }

    








    public function archiveBenefits(?int $id, User $currentUser, bool $updateApplicationHistory = true): void
    {
        $this->archiveAccountingBenefit(
            $id,
            $currentUser,
            BachelorPreferences::class,
            'Невозможно удалить файл льготы или преимущественного права.',
            'Невозможно удалить льготу или преимущественное право.',
            $updateApplicationHistory
        );
    }

    




    protected function initBenefit(BachelorApplication $application): BachelorPreferences
    {
        return $this->initBachelorPreferences($application, BachelorPreferences::TYPE_PREF);
    }

    








    private function loadBenefitsLists(BachelorApplication $application): array
    {
        $items = [];

        $dictionaryBenefitsLists = [
            'privilege' => Privilege::class,
            'specialMark' => SpecialMark::class,
        ];

        $application->archiveAdmissionCampaignHandler->handle();
        foreach ($dictionaryBenefitsLists as $innerJoinTable => $class) {
            $items = array_merge(
                $items,
                $this->getPrivilegeOrSpecialMarkDictionaryDatas($application, $class, $innerJoinTable)
            );
        }

        return $items;
    }

    






    private function getPrivilegeOrSpecialMarkDictionaryDatas(
        $application,
        $class,
        $innerJoinTable
    ): array {
        BenefitsService::checkIsCorrectDictionaryBenefitsClass($class);

        $items = [];

        $tnClass = $class::tableName();
        $tnAdmissionProcedure = AdmissionProcedure::tableName();
        $tnAdmissionCampaignReferenceType = StoredAdmissionCampaignReferenceType::tableName();
        $campaignReferenceUid = $application->type->rawCampaign->referenceType->reference_uid;

        $subQuery = $application
            ->getPreferences()
            ->innerJoinWith([$innerJoinTable => function ($q) {
                
                $q->innerJoinWith('admissionProcedures', false);
            }])
            ->select("{$tnClass}.id");

        $result = $class::find()
            ->notMarkedToDelete()
            ->active()
            ->joinWith('admissionProcedures', false)
            ->joinWith('admissionProcedures.admissionCampaignRef', false)
            ->andWhere(["{$tnClass}.is_folder" => false])
            ->andWhere(["{$tnClass}.archive" => false])
            ->andWhere(["{$tnAdmissionProcedure}.archive" => false])
            ->andWhere(["{$tnAdmissionCampaignReferenceType}.reference_uid" => $campaignReferenceUid])
            ->orFilterWhere(["{$tnClass}.id" => $subQuery])
            ->orderBy("{$tnClass}.description")
            ->all();
        foreach ($result as $value) {
            $items[$value->getHashCode()] = $value->description;
        }

        return $items;
    }

    






    private static function checkIsCorrectDictionaryBenefitsClass(string $benefitsClass): bool
    {
        $correctBenefitsClasses = [
            Privilege::class,
            SpecialMark::class,
        ];
        if (in_array(
            $benefitsClass,
            $correctBenefitsClasses
        )) {
            return true;
        }

        throw new UserException("Был передан класс не относящийся к категории «Льгот и преимущественного права» ({$benefitsClass})");
    }
}
