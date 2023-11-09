<?php

namespace common\services\abiturientController\bachelor\accounting_benefits;

use common\components\configurationManager;
use common\components\RegulationRelationManager;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;
use common\models\dictionary\Olympiad;
use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\EmptyCheck;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use yii\caching\CacheInterface;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class OlympiadsService extends AccountingBenefitsService
{
    





    public function getOlympiadsListSelect(int $applicationId, int $specialityId): array
    {
        $output = [];
        $params = $this->request->post('depdrop_parents');

        if (!empty($params) && isset($params[0]) && filter_var($params[0], FILTER_VALIDATE_BOOLEAN)) {
            $tnBachelorSpeciality = BachelorSpeciality::tableName();
            $application = $this->getApplication($applicationId);
            $speciality = $application->getSpecialitiesWithoutOrdering()
                ->andWhere(["{$tnBachelorSpeciality}.id" => $specialityId])
                ->one();

            
            $items = $application
                ->getBachelorPreferencesOlympForBVI()
                ->with(['olympiad'])
                ->all();

            $output = $this->makeDataFormattedForDepDrop(
                function ($item) use ($speciality) {
                    $matched = $item->olympiadMatchedByCurriculum(ArrayHelper::getValue($speciality, 'speciality.curriculumRef'));
                    $tmp = [
                        'id' => ArrayHelper::getValue($item, 'id'),
                        'name' => ArrayHelper::getValue($item, 'name'),
                    ];
                    if (!$matched) {
                        $tmp['options'] = ['data-not-matched-curriculum' => 1];
                    }

                    return $tmp;
                },
                $items
            );
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

    





    public function getOlympTypeDataForSelect(?int $app_id, ?int $id = null): array
    {
        $output = [];
        $selected = '';
        $application = $this->getApplication($app_id);

        if (!isset($_POST['depdrop_parents'])) {
            return ['output' => $output, 'selected' => $selected];
        }

        $params = $_POST['depdrop_parents'];
        if (EmptyCheck::isLoadingStringOrEmpty($params[0])) {
            return ['output' => $output, 'selected' => $selected];
        }

        $olympic = null;
        $selected = '';
        if ($id) {
            $olympic = BachelorPreferences::findOne(['id_application' => $app_id, 'id' => $id]);
        }
        if (isset($olympic)) {
            $selected = $olympic->special_mark_id;
        }

        $output = $this->getCachedOlympSpecialMarks($application, $params[0]);

        return ['output' => $output, 'selected' => $selected];
    }

    




    public function getDocTypeOlympiadsDataForSelect(?int $app_id = null): array
    {
        $output = [];
        $selected = '';
        $application = $this->getApplication($app_id);

        if ($this->request->post('depdrop_parents')) {
            $params = $this->request->post('depdrop_parents');
            $specMark = null;
            $benefit = null;

            if (!EmptyCheck::isLoadingStringOrEmpty($params[0])) {
                $specMark = SpecialMark::findOne((int)$params[0]);
                $benefit = BachelorPreferences::findOne([
                    'id_application' => $app_id,
                    'special_mark_id' => $specMark->id
                ]);

                if (isset($benefit)) {
                    $selected = $benefit->document_type_id;
                }
            }

            $output = $this->makeDataFormattedForDepDrop(
                function ($item) {
                    return [
                        'id' => $item['maxid'],
                        'name' => $item['description']
                    ];
                },
                $this->getDocTypesByBachelorPreferenceCodeAndBachelorApplication($application, $specMark)
            );

            if (!is_null($benefit) && $benefit->document_type_id && !array_filter($output, function ($item) use ($benefit) {
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

        return ['output' => $output, 'selected' => $selected];
    }

    






    public function getOlympiads(?int $id)
    {
        $application = $this->getApplication($id);
        $model = $this->initOlympiad($application);
        $campaign_ref_uid = $application->type->rawCampaign->referenceType->reference_uid;

        $items = $this->cache->getOrSet(
            "olympiadArray{$campaign_ref_uid}",
            function () use ($campaign_ref_uid) {
                return $this->getOlympiadsForCache($campaign_ref_uid);
            },
            3600
        );

        $itemsOlymp = array_reduce($this->getCachedOlympSpecialMarks($application), function ($carry, array $item) {
            $carry[$item['id']] = $item['name'];
            return $carry;
        }, []);

        $itemsDoc = $this->getDocumentItems();

        
        $olympiads = $application
            ->getBachelorPreferencesOlymp()
            ->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $olympiads,
            'pagination' => ['pageSize' => 10]
        ]);

        $canEdit = ($application->canEdit() && $application->canEditSpecialities());

        return [
            'id' => $id,
            'model' => $model,
            'items' => $items,
            'canEdit' => $canEdit,
            'itemsDoc' => $itemsDoc,
            'providers' => $olympiads,
            'itemsOlymp' => $itemsOlymp,
            'dataProvider' => $dataProvider,
            'action' => '/site/accounting-olympiads'
        ];
    }

    




    public function getRegulationsAndAttachmentsForOlympiad(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_OLYMPIAD,
            RegulationRelationManager::RELATED_ENTITY_OLYMPIAD
        );
    }

    








    public function editOlympiads(User $currentUser): array
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
            ->notInEnlistedAppWithOlympiad()
            ->one();

        if (isset($accountingBenefits)) {
            return $this->updateOlympicFromPost(
                $currentUser,
                $application,
                $accountingBenefits
            );
        }

        return [null, false, false];
    }

    







    public function saveNewOlympiads(User $currentUser, ?int $id): array
    {
        $formName = (new BachelorPreferences)->formName();
        $application = $this->saveNewAccountingBenefits($id, $formName);
        $newAccountingBenefits = $this->initOlympiad($application);

        return $this->updateOlympicFromPost(
            $currentUser,
            $application,
            $newAccountingBenefits
        );
    }

    






    public function canDownloadOlympiads(?int $id = null): bool
    {
        return $this->canGenerateFilesToDownloadAccountingBenefits($id, BachelorPreferences::class);
    }

    






    public function updateOlympicFromPost(
        User $currentUser,
        BachelorApplication $application,
        BachelorPreferences $model
    ): array {
        return $this->updateAccountingBenefitsFromPost(
            $currentUser,
            $application,
            $model,
            function ($model) {
                $model->privilege_code = null;
                $model->privilege_id = null;
                $model->priority_right = false;
                $model->individual_value = false;
                $model->special_mark_code = ArrayHelper::getValue($model, 'specialMark.code');
                $model->olympiad_code = ArrayHelper::getValue($model, 'olympiad.code');
                $model->document_type = ArrayHelper::getValue($model, 'documentType.code');

                return $model;
            },
            'Ошибка при редактировании олимпиад:'
        );
    }

    








    public function getFilterOlympiadsForCache(
        string $year,
        string $profile_uid,
        string $campaign_ref_uid,
        string $class_uid,
        string $kind_name
    ): string {
        $query = Olympiad::find()
            ->joinWith('olympiadFilters olympiad_filters', false)
            ->leftJoin(StoredAdmissionCampaignReferenceType::tableName() . ' campaign_ref', 'campaign_ref.id = olympiad_filters.campaign_ref_id')
            ->andWhere(['campaign_ref.reference_uid' => $campaign_ref_uid])
            ->andWhere(['dictionary_olympiads.archive' => false])
            ->andWhere(['olympiad_filters.archive' => false]);
        if ($kind_name) {
            $query->joinWith(['olympicKindRef olympic_kind_ref'])
                ->andWhere(['olympic_kind_ref.reference_name' => $kind_name]);
        }
        if ($class_uid) {
            $query->joinWith(['olympicClassRef olympic_class_ref'])
                ->andWhere(['olympic_class_ref.reference_uid' => $class_uid]);
        }
        if ($profile_uid) {
            $query->joinWith(['olympicProfileRef olympic_profile_ref'])
                ->andWhere(['olympic_profile_ref.reference_uid' => $profile_uid]);
        }
        if ($year) {
            $query->andWhere(['dictionary_olympiads.year' => $year]);
        }

        $output = $this->makeDataFormattedForDepDrop(
            function ($o) {
                return [
                    'id' => $o->id,
                    'name' => $o->getFullName()
                ];
            },
            $query->all()
        );

        return json_encode(['output' => $output, 'selected' => '']);
    }

    




    protected function initOlympiad(BachelorApplication $application): BachelorPreferences
    {
        return $this->initBachelorPreferences($application, BachelorPreferences::TYPE_OLYMP);
    }

    




    private function getOlympiadsForCache(string $campaignRefUid): array
    {
        $items = [];

        $olympiadArray = Olympiad::find()
            ->where([
                Olympiad::tableName() . '.id' => Olympiad::find()
                    ->select([Olympiad::tableName() . '.id'])
                    ->joinWith('olympiadFilters', false)
                    ->joinWith('olympiadFilters.campaignRef', false)
                    ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => $campaignRefUid])
                    ->andWhere(['dictionary_olympiads.archive' => false])
                    ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.archive' => false])
                    ->groupBy([Olympiad::tableName() . '.id'])
            ])
            ->all();

        foreach ($olympiadArray as $value) {
            $items[$value->id] = $value->fullName;
        }
        return $items;
    }

    





    private function getOlympSpecialMarksForCache(int $olympiadId = null, string $campaignRefUid): array
    {
        $possible_special_mark_ids = OlympiadFilter::find()
            ->select('special_mark_id')
            ->joinWith('campaignRef')
            ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.archive' => false])
            ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => $campaignRefUid])
            ->andFilterWhere([OlympiadFilter::tableName() . '.olympiad_id' => $olympiadId])
            ->andWhere([OlympiadFilter::tableName() . '.variant_of_retest_ref_id' => $this->configurationManager->getCode('without_entrant_tests_variant')]);

        $tnSpecialMark = SpecialMark::tableName();
        $result_query = SpecialMark::find()
            ->notMarkedToDelete()
            ->active()
            ->select(['maxid' => 'max(id)', 'code', 'description'])
            ->andWhere(["{$tnSpecialMark}.id" => $possible_special_mark_ids])
            ->andWhere(["{$tnSpecialMark}.archive" => false])
            ->groupBy(['code', 'description']);

        
        if (!$result_query->exists()) {
            $result_query = SpecialMark::find()
                ->notMarkedToDelete()
                ->active()
                ->select(['maxid' => 'max(id)', 'code', 'description'])
                ->andWhere(["{$tnSpecialMark}.archive" => false])
                ->groupBy(['code', 'description']);
        }

        $output = $this->makeDataFormattedForDepDrop(
            function (array $value) {
                return [
                    'id' => $value['maxid'],
                    'name' => $value['description']
                ];
            },
            $result_query
                ->asArray()
                ->orderBy("{$tnSpecialMark}.description")
                ->all()
        );

        return  $output;
    }

    





    private function getCachedOlympSpecialMarks(BachelorApplication $application, int $olympiad_id = null): array
    {
        $campaign_ref_uid = $application->type->rawCampaign->referenceType->reference_uid;

        return $this->cache->getOrSet(
            "getOlympSpecialMarks{$campaign_ref_uid}{$olympiad_id}",
            function () use ($olympiad_id, $campaign_ref_uid) {
                return $this->getOlympSpecialMarksForCache($olympiad_id, $campaign_ref_uid);
            },
            3600
        );
    }
}
