<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ParentDataBuilders;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\AddressFullPackageBuilder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\parentData\ParentAddressData;
use common\modules\abiturient\models\parentData\ParentData;

class ParentAddressFullPackageBuilder extends AddressFullPackageBuilder
{
    private $parent_data;

    public function __construct(AbiturientQuestionary $questionary, ?AddressData $addressData, ParentData $parent_data)
    {
        parent::__construct($questionary, $addressData);
        $this->parent_data = $parent_data;
    }

    protected function getAddressDataForUpdate(?string $type): ParentAddressData
    {
        $addressData = $this->address_data;
        if (empty($addressData)) {
            
            $addressData = new ParentAddressData();
            
        }

        return $addressData;
    }

    protected function setExternalLinks(AddressData $address_data)
    {
        DraftsManager::ensurePersisted($address_data);
        $this->parent_data->address_data_id = $address_data->id;
    }
}
