<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ParentDataBuilders;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\AddressFullPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\PersonalDataFullPackageBuilder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\parentData\ParentAddressData;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\parentData\ParentPersonalData;
use common\modules\abiturient\models\PersonalData;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class ParentPersonalDataFullPackageBuilder extends PersonalDataFullPackageBuilder
{
    private $parent_data;

    public function __construct(AbiturientQuestionary $questionary, ParentData $parent_data, ?BachelorApplication $application = null)
    {
        parent::__construct($questionary, $application);
        $this->parent_data = $parent_data;
    }

    public function getPersonalData(): ParentPersonalData
    {
        return $this->parent_data->personalData;
    }

    public function getEmail(): ?string
    {
        return $this->parent_data->email;
    }

    protected function buildEntrantCodes(): array
    {
        return [];
    }

    protected function buildAdditionalElements(): array
    {
        return [];
    }

    protected function buildContactInformation()
    {
        $tmp = (new AddressFullPackageBuilder($this->questionary, $this->parent_data->addressData))->build();
        return ArrayHelper::merge(['ContactInformationType' => 'RegistrationAddress'], $tmp);
    }

    protected function buildIdentificateDocuments()
    {
        return (new ParentPassportDataFullPackageBuilder($this->questionary, $this->parent_data, $this->application))
            ->setFilesSyncer($this->files_syncer)
            ->build();
    }

    protected function getPersonalDataForUpdate(): ?ParentPersonalData
    {
        $personal_data = $this->parent_data->personalData;

        if (empty($personal_data)) {
            $personal_data = new ParentPersonalData();
        }

        return $personal_data;
    }

    protected function setExternalLinks(PersonalData $personal_data)
    {
        DraftsManager::ensurePersisted($personal_data);
        $this->parent_data->personal_data_id = $personal_data->id;
    }

    protected function getRegistrationAddressDataForUpdate(): ?ParentAddressData
    {
        return $this->parent_data->getAddressData()->one();
    }

    protected function updateEmail($email)
    {
        $this->parent_data->email = $email;
    }

    protected function getAddressDataModel(?string $type): ?ParentAddressData
    {
        return $this->getRegistrationAddressDataForUpdate();
    }

    protected function updateEntrantCodes(PersonalData $personal_data, array $raw_data): void
    {
    }

    protected function updateAdditionalElements(PersonalData $personal_data, array $raw_data): void
    {
    }

    protected function updateAddresses($raw_data)
    {
        if (isset($raw_data['ContactInformation'])) {
            $contact_info = (ArrayHelper::isAssociative($raw_data['ContactInformation']) ? [$raw_data['ContactInformation']] : $raw_data['ContactInformation']);

            foreach ($contact_info as $raw_address) {
                $address_data = $this->getAddressDataModel(ArrayHelper::getValue($raw_address, 'ContactInformationType'));
                if (!(new ParentAddressFullPackageBuilder($this->questionary, $address_data, $this->parent_data))
                    ->update($raw_address)) {
                    throw new UserException("Не удалось сохранить данные об адресе регистрации");
                }
            }
        }
    }

    protected function updatePassports($raw_data)
    {
        if (!(new ParentPassportDataFullPackageBuilder($this->questionary, $this->parent_data, $this->application))
            ->setFilesSyncer($this->files_syncer)
            ->setAllowDirectFetching($this->allow_direct_fetching)
            ->update($raw_data['IdentificateDocuments'] ?? [])) {
            throw new UserException("Не удалось сохранить данные о паспортах");
        }
    }
}
