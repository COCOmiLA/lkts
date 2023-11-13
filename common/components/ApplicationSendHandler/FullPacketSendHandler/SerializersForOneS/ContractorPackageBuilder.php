<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\AddressHelper\AddressHelper;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\Contractor;
use common\models\dictionary\Country;
use common\models\dictionary\Fias;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use Yii;

class ContractorPackageBuilder extends BaseApplicationPackageBuilder
{
    protected ?Contractor $contractor;

    public function __construct(?BachelorApplication $application, ?Contractor $contractor)
    {
        parent::__construct($application);
        $this->contractor = $contractor;
    }

    public function build()
    {
        $contractor = [
            'ContractorRef' => ReferenceTypeManager::GetReference($this->contractor, 'contractorRef'),
            'SubdivisionCode' => ($this->contractor->subdivision_code ?? ''),
            'ContractorTypeRef' => ReferenceTypeManager::GetReference($this->contractor, 'contractorTypeRef'),
        ];

        $location_code = null;
        $location_name = null;
        if ($this->contractor) {
            if ($this->contractor->location_not_found) {
                $location_name = $this->contractor->location_name;
            } else {
                $location_code = $this->contractor->location_code;
            }
        }
        
        
        if ($location_code || $location_name) {
            $contractor['ContactInformation'] = static::buildAddress($location_code, $location_name);
        }

        return $contractor;
    }

    public static function buildAddress(?string $location_code = null, ?string $location_name = null): array
    {
        $country = null;
        $region = null;
        $area = null;
        $city = null;
        $village = null;

        if ($location_code || $location_name) {
            
            $country = Country::findByUID(Yii::$app->configurationManager->getCode('russia_guid'));
        }

        if ($location_code) {
            $location = Fias::find()->andWhere([
                'code' => $location_code
            ])->one();

            if ($location) {
                $region = Fias::find()->andWhere([
                    'region_code' => $location->region_code,
                    'address_element_type' => AddressHelper::REGION_TYPE
                ])->one();
        
                $area = Fias::find()->andWhere([
                    'region_code' => $location->region_code,
                    'area_code' => $location->area_code,
                    'address_element_type' => AddressHelper::AREA_TYPE
                ])->one();
        
    
                if ($location->address_element_type === AddressHelper::TOWN_TYPE) {
                    $village = $location;
                }
                if ($location->address_element_type === AddressHelper::CITY_TYPE) {
                    $city = $location;
                }
            }
        }

        return [
            'ContactInformationType' => 'RegistrationAddress',
            'Index' => '',
            'Country' => $country ? ReferenceTypeManager::GetReference($country) : ReferenceTypeManager::getEmptyRefTypeArray(),
            'Region' => $region ? "{$region->name} {$region->short}" : '',
            'Area' => $area ? "{$area->name} {$area->short}" : '',
            'City' => $city ? "{$city->name} {$city->short}" : $location_name,
            'Place' => $village ? "{$village->name} {$village->short}" : '',
            'Street' => '',
            'House' => '',
            'Building' => '',
            'Apartment' => '',
        ];
    }

    public function update($raw_data)
    {
        
        
        
        return true;
    }
}