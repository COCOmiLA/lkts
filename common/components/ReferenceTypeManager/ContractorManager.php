<?php

namespace common\components\ReferenceTypeManager;

use common\components\AddressHelper\AddressHelper;
use common\models\dictionary\Contractor;
use common\models\dictionary\Country;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredContractorReferenceType;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\PassportData;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ContractorManager
{
    public static function GetOrCreateContractor(array $raw_contractor, ?int $contractor_id = null): ?Contractor
    {
        if (EmptyCheck::isEmpty($raw_contractor)) {
            return null;
        }

        $raw_contractor_ref = ArrayHelper::getValue($raw_contractor, 'ContractorRef');
        $raw_contractor_type_ref = ArrayHelper::getValue($raw_contractor, 'ContractorTypeRef');

        if (EmptyCheck::isEmpty($raw_contractor_ref)) {
            return null;
        }

        if (EmptyCheck::isEmpty($raw_contractor_type_ref)) {
            return null;
        }

        $contractor_ref = ReferenceTypeManager::GetOrCreateReference(
            StoredContractorReferenceType::class,
            $raw_contractor_ref
        );

        $contractor_type_ref = ReferenceTypeManager::GetOrCreateReference(
            StoredContractorTypeReferenceType::class,
            $raw_contractor_type_ref
        );

        $region = null;
        $area = null;
        $city = null;
        $town = null;

        $location_not_found = true;
        $country = ReferenceTypeManager::GetOrCreateReference(Country::class, ArrayHelper::getValue($raw_contractor, 'ContactInformation.Country'));
        if (!empty($country)) {
            if ($country->ref_key === Yii::$app->configurationManager->getCode('russia_guid') || !Yii::$app->configurationManager->getCode('russia_guid')) {
                $region = AddressHelper::getRegion(ArrayHelper::getValue($raw_contractor, 'ContactInformation.Region'))->getOne();
                $area = AddressHelper::getArea($region, ArrayHelper::getValue($raw_contractor, 'ContactInformation.Area'))->getOne();
                $city = AddressHelper::getCity($region, $area, ArrayHelper::getValue($raw_contractor, 'ContactInformation.City'))->getOne();
                $town = AddressHelper::getTown($region, $area, $city, ArrayHelper::getValue($raw_contractor, 'ContactInformation.Place'))->getOne();
                if ($city || $town || ($region && in_array($region->code, AddressHelper::federalSignificanceCityCodes()))) {
                    $location_not_found = false;
                }
            }
        }
        $city_name = ArrayHelper::getValue($raw_contractor, 'ContactInformation.City');
        $town_name = ArrayHelper::getValue($raw_contractor, 'ContactInformation.Place');

        if ($region && in_array($region->code, AddressHelper::federalSignificanceCityCodes())) {
            $location_code = $region->code;
        } else {
            $location_code = $city->code ?? $town->code ?? null;
        }

        $location_name = null;
        if ($location_not_found) {
            $location_name = $city_name ?? $town_name ?? null;
        }

        $query = Contractor::find();
        $query = self::buildCompareConditions($query, [
            'name' => ArrayHelper::getValue($raw_contractor, 'ContractorRef.ReferenceName'),
            'contractor_reference_uid' => ArrayHelper::getValue($contractor_ref, 'reference_uid'),
            'contractor_type_reference_uid' => ArrayHelper::getValue($contractor_type_ref, 'reference_uid'),
            'subdivision_code' => ArrayHelper::getValue($raw_contractor, 'SubdivisionCode'),
            'location_code' => $location_code,
            'location_name' => $location_name,
        ]);
        $model = $query->one();

        
        if ($model === null && isset($contractor_id)) {
            $model = Contractor::findOne($contractor_id);
        }

        
        if ($model === null) {
            $model = new Contractor();
        }

        $model->name = ArrayHelper::getValue($raw_contractor, 'ContractorRef.ReferenceName');
        $model->contractor_ref_id = $contractor_ref->id;
        $model->subdivision_code = ArrayHelper::getValue($raw_contractor, 'SubdivisionCode');
        $model->status = Contractor::STATUS_APPROVED;
        $model->archive = false;
        $model->contractor_type_ref_id = $contractor_type_ref->id ?? null;
        $model->location_code = $location_code;
        $model->location_name = $location_name;
        $model->location_not_found = $location_not_found;

        if (!$model->save()) {
            throw new RecordNotValid($model);
        }

        static::LinkEntitiesToApprovedContractor($model);

        return $model;
    }

    public static function buildCompareConditions(ActiveQuery $query, array $attributes): ActiveQuery
    {
        $query->joinWith('contractorRef contractor_ref', false);
        $query->joinWith('contractorTypeRef contractor_type_ref', false);

        
        $query->andFilterWhere(['contractor_ref.reference_uid' => $attributes['contractor_reference_uid'] ?? null]);
        
        $conditions = [
            'name' => $attributes['name'] ?? null,
            'contractor_type_ref.reference_uid' => $attributes['contractor_type_reference_uid'] ?? null,
        ];

        if (!empty($attributes['subdivision_code'])) {
            $conditions['subdivision_code'] = $attributes['subdivision_code'];
        } else {
            $conditions['subdivision_code'] = [null, ''];
        }

        if (!empty($attributes['location_code'])) {
            $conditions['location_code'] = $attributes['location_code'];
        } else {
            $conditions['location_code'] = [null, ''];
        }

        if (!empty($attributes['location_name'])) {
            $conditions['location_name'] = $attributes['location_name'];
        } else {
            $conditions['location_name'] = [null, ''];
        }

        $query->andWhere($conditions);

        return $query;
    }

    






    public static function Upsert(array $attributes, DocumentType $documentTypeForValidation): Contractor
    {
        [
            'name' => $name,
            'contractor_type_ref_id' => $contractor_type_ref_id,
            'subdivision_code' => $subdivision_code,
        ] = $attributes;

        
        $contractor_type_ref = StoredContractorTypeReferenceType::findOne($contractor_type_ref_id);
        
        $query = Contractor::find();
        $query = static::buildCompareConditions($query, [
            'name' => $name ?? null,
            'contractor_type_reference_uid' => $contractor_type_ref->reference_uid ?? null,
            'subdivision_code' => $subdivision_code ?? null,
            'location_code' => $attributes['location_code'] ?? null,
            'location_name' => $attributes['location_name'] ?? null,
        ])->andWhere([Contractor::tableName() . '.archive' => false]);

        $model = $query->one();

        if ($model === null) {
            $model = new Contractor();
            $model->status = Contractor::STATUS_PENDING;
        }

        $model->setDocumentTypeForValidation($documentTypeForValidation);
        $model->load($attributes, '');

        if (!$model->save()) {
            throw new RecordNotValid($model);
        }

        return $model;
    }

    public static function LinkEntitiesToApprovedContractor(Contractor $model)
    {
        Contractor::linkToApproved(PassportData::class, 'contractor', $model);
        Contractor::linkToApproved(EducationData::class, 'contractor', $model);
        Contractor::linkToApproved(BachelorPreferences::class, 'contractor', $model);
        Contractor::linkToApproved(BachelorTargetReception::class, 'documentContractor', $model);
        Contractor::linkToApproved(BachelorTargetReception::class, 'targetContractor', $model);
        Contractor::linkToApproved(IndividualAchievement::class, 'contractor', $model);
    }
}
