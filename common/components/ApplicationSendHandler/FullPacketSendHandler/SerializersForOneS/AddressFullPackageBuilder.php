<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\AddressHelper\AddressHelper;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\Country;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ActualAddressData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\drafts\DraftsManager;
use Yii;
use yii\helpers\ArrayHelper;

class AddressFullPackageBuilder extends BaseQuestionaryPackageBuilder
{
    const TYPE_REGISTRATION = 'RegistrationAddress';
    const TYPE_RESIDENCE = 'ResidenceAddress';

    protected $address_data;

    public function __construct(AbiturientQuestionary $questionary, ?AddressData $addressData)
    {
        parent::__construct($questionary);
        $this->address_data = $addressData;
    }

    public function build()
    {
        $addressData = $this->address_data;
        return [
            'Index' => $addressData->postal_index,
            'Country' => ReferenceTypeManager::GetReference($addressData, 'country'),
            'Region' => $addressData->region ? "{$addressData->region->name} {$addressData->region->short}" : $addressData->region_name,
            'Area' => $addressData->area ? "{$addressData->area->name} {$addressData->area->short}" : $addressData->area_name,
            'City' => $addressData->city ? "{$addressData->city->name} {$addressData->city->short}" : $addressData->city_name,
            'Place' => $addressData->village ? "{$addressData->village->name} {$addressData->village->short}" : $addressData->town_name,
            'Street' => $addressData->street ? "{$addressData->street->name} {$addressData->street->short}" : $addressData->street_name,
            'House' => $addressData->house_number,
            'Building' => $addressData->housing_number,
            'Apartment' => $addressData->flat_number,
        ];
    }

    protected function getAddressDataForUpdate(?string $type): AddressData
    {
        $addressData = $this->address_data;
        if (empty($addressData)) {
            $addressData = ($type === AddressFullPackageBuilder::TYPE_REGISTRATION ? new AddressData() : new ActualAddressData());
            $addressData->questionary_id = $this->questionary->id;
        }
        return $addressData;
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);

        if (empty($raw_data)) {
            return false;
        }

        $addressData = $this->getAddressDataForUpdate(ArrayHelper::getValue($raw_data, 'ContactInformationType'));

        $addressData->postal_index = ArrayHelper::getValue($raw_data, 'Index');
        $russia_guid = Yii::$app->configurationManager->getCode('russia_guid');
        $country = ReferenceTypeManager::GetOrCreateReference(Country::class, ArrayHelper::getValue($raw_data, 'Country'));
        $addressData->country_id = ArrayHelper::getValue($country, 'id');

        $addressData->not_found = false;
        $addressData->homeless = false;
        if (!empty($country) && $country->ref_key == $russia_guid) {
            $region = AddressHelper::getRegion(ArrayHelper::getValue($raw_data, 'Region'))->getOne();
            $area = AddressHelper::getArea($region, ArrayHelper::getValue($raw_data, 'Area'))->getOne();
            $city = AddressHelper::getCity($region, $area, ArrayHelper::getValue($raw_data, 'City'))->getOne();
            $town = AddressHelper::getTown($region, $area, $city, ArrayHelper::getValue($raw_data, 'Place'))->getOne();
            $street = AddressHelper::getStreet($region, $area, $city, $town, ArrayHelper::getValue($raw_data, 'Street'))->getOne();

            AddressData::setAddressProperty(
                $addressData,
                $region,
                ArrayHelper::getValue($raw_data, 'Region'),
                'region_id',
                'region_name',
                true,
                true);
            AddressData::setAddressProperty(
                $addressData,
                $area,
                ArrayHelper::getValue($raw_data, 'Area'),
                'area_id',
                'area_name');
            AddressData::setAddressProperty(
                $addressData,
                $city,
                ArrayHelper::getValue($raw_data, 'City'),
                'city_id',
                'city_name');
            AddressData::setAddressProperty(
                $addressData,
                $town,
                ArrayHelper::getValue($raw_data, 'Place'),
                'village_id',
                'town_name');
            AddressData::setAddressProperty(
                $addressData,
                $street,
                ArrayHelper::getValue($raw_data, 'Street'),
                'street_id',
                'street_name');
            $addressData->processKLADRCode();
        } else {
            $addressData->not_found = true;
            $addressData->region_name = ArrayHelper::getValue($raw_data, 'Region');
            $addressData->area_name = ArrayHelper::getValue($raw_data, 'Area');
            $addressData->city_name = ArrayHelper::getValue($raw_data, 'City');
            $addressData->town_name = ArrayHelper::getValue($raw_data, 'Place');
            $addressData->street_name = ArrayHelper::getValue($raw_data, 'Street');
        }
        $addressData->house_number = ArrayHelper::getValue($raw_data, 'House');
        $addressData->housing_number = ArrayHelper::getValue($raw_data, 'Building');
        $addressData->flat_number = ArrayHelper::getValue($raw_data, 'Apartment');
        $addressData->isFrom1C = true;
        $addressData->cleanUnusedAttributes();
        DraftsManager::SuspendHistory($addressData);
        
        $addressData->loadDefaultValues()->save(false);
        $this->setExternalLinks($addressData);

        return true;
    }

    protected function setExternalLinks(AddressData $address_data)
    {
        return;
    }
}