<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\Country;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\Gender;
use common\models\SendingFile;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\PersonalData;
use common\modules\abiturient\models\ReceivingFile;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class PersonalDataFullPackageBuilder extends BaseQuestionaryPackageBuilder
{
    
    protected $application;

    protected $fetch_email = false;
    protected $need_hostel = false;

    public function __construct(AbiturientQuestionary $questionary, ?BachelorApplication $application = null)
    {
        parent::__construct($questionary);
        $this->application = $application;
    }

    public function setFetchEmail(bool $fetch_email): PersonalDataFullPackageBuilder
    {
        $this->fetch_email = $fetch_email;
        return $this;
    }

    protected bool $allow_direct_fetching = false;

    public function setAllowDirectFetching(bool $allow_direct_fetching): PersonalDataFullPackageBuilder
    {
        $this->allow_direct_fetching = $allow_direct_fetching;
        return $this;
    }

    public function build()
    {
        $personal_data = $this->getPersonalData();

        $genderRef = ReferenceTypeManager::GetReference($personal_data->relGender);

        $main_props = [
            'Name' => $personal_data->firstname,
            'Surname' => $personal_data->lastname,
            'Patronymic' => $personal_data->middlename,
            'NameEng' => '',
            'SurnameEng' => '',
            'SNILS' => $personal_data->snils,
            'GenderRef' => $genderRef,
            'Birthday' => (string)$personal_data->formated_birthdate,
            'Birthplace' => $personal_data->birth_place ?? '',
            'CitizenshipRef' => ReferenceTypeManager::GetReference($personal_data, 'citizenship'),
            'Email' => $this->getEmail(),
            'PhoneMobile' => $personal_data->main_phone,
            'PhoneHome' => $personal_data->secondary_phone,
            'ContactInformation' => $this->buildContactInformation(),
            'LanguageRef' => ReferenceTypeManager::GetReference($personal_data->language),
            'IdentificateDocuments' => $this->buildIdentificateDocuments(),
        ];
        $entrant_codes = $this->buildEntrantCodes();

        $additional_elements = $this->buildAdditionalElements();

        return ArrayHelper::merge($main_props, $entrant_codes, $additional_elements);
    }

    protected function buildEntrantCodes(): array
    {
        $personal_data = $this->getPersonalData();
        return [
            'EntrantUniqueCode' => $personal_data->entrant_unique_code,
            'EntrantUniqueCodeSpecialQuota' => $personal_data->entrant_unique_code_special_quota,
        ];
    }

    protected function buildAdditionalElements(): array
    {
        return PersonalDataFullPackageBuilder::buildPhotoData($this->questionary, $this->files_syncer->isFilesSyncing());
    }

    public static function buildPhotoData(AbiturientQuestionary $questionary, bool $send_binary): array
    {
        $avatarFileName = "";
        $avatarFileExt = "";
        $avatarFileParts = [];
        $result = [];
        $avatar = $questionary->abiturientAvatar;
        if ($avatar && $avatar->fileExists()) {
            $avatarFileExt = $avatar->extension;
            $avatarFileName = pathinfo($avatar->filename)['filename'];

            if ($send_binary) {
                $avatarSend = new SendingFile($avatar->linkedFile);
                
                if ($avatarSend->saveFileTo1C()) {
                    
                    $avatarFileParts = $avatarSend->getPartsArraysTo1C();
                }
            }
        }
        $result['PhotoFileName'] = $avatarFileName;
        $result['PhotoFileExt'] = $avatarFileExt;

        $result['PhotoFilePartsCount'] = ArrayHelper::getValue($avatar, 'linkedFile.partsCount', '');
        $result['PhotoFileHash'] = ArrayHelper::getValue($avatar, 'linkedFile.content_hash', '');
        $result['PhotoFileUid'] = ArrayHelper::getValue($avatar, 'linkedFile.uid', '');

        if ($avatarFileParts) {
            $result['PhotoFileParts'] = $avatarFileParts;
        }
        return $result;
    }

    public function getEmail(): ?string
    {
        return $this->questionary->user->email;
    }

    public function getPersonalData(): PersonalData
    {
        return $this->questionary->personalData;
    }

    protected function buildContactInformation()
    {
        $result = [];
        $tmp = (new AddressFullPackageBuilder($this->questionary, $this->questionary->addressData))->build();
        $result[] = ArrayHelper::merge(['ContactInformationType' => 'RegistrationAddress'], $tmp);

        if ($this->questionary->actualAddressData) {
            $tmp = (new AddressFullPackageBuilder($this->questionary, $this->questionary->actualAddressData))->build();
            $result[] = ArrayHelper::merge(['ContactInformationType' => 'ResidenceAddress'], $tmp);
        }

        return $result;
    }

    protected function buildIdentificateDocuments()
    {
        return (new PassportsFullPackageBuilder($this->questionary, $this->application))
            ->setFilesSyncer($this->files_syncer)
            ->build();
    }

    


    protected function getPersonalDataForUpdate(): ?PersonalData
    {
        $questionary = $this->questionary;
        $personal_data = ArrayHelper::getValue($questionary, 'personalData');
        if (empty($personal_data)) {
            $personal_data = new PersonalData();
            $personal_data->questionary_id = $questionary->id;
        }

        return $personal_data;
    }

    protected function getRegistrationAddressDataForUpdate(): ?AddressData
    {
        $questionary = $this->questionary;
        return $questionary->addressData;
    }

    protected function getResidenceAddressDataForUpdate(): ?AddressData
    {
        $questionary = $this->questionary;
        return $questionary->actualAddressData;
    }

    protected function getAddressDataModel(?string $type): ?AddressData
    {
        switch ($type) {
            case AddressFullPackageBuilder::TYPE_REGISTRATION:
                return $this->getRegistrationAddressDataForUpdate();
            case AddressFullPackageBuilder::TYPE_RESIDENCE:
                return $this->getResidenceAddressDataForUpdate();
            default:
                throw new UserException("Не удалось определить тип адреса");
        }
    }

    protected function updateEmail($email)
    {
        
        if (!$this->questionary->user->email || $this->fetch_email) {
            $this->questionary->user->email = $email;
        }
    }

    protected function updateAddresses($raw_data)
    {
        if (isset($raw_data['ContactInformation'])) {
            $contact_info = (ArrayHelper::isAssociative($raw_data['ContactInformation']) ? [$raw_data['ContactInformation']] : $raw_data['ContactInformation']);

            foreach ($contact_info as $raw_address) {
                $address_data = $this->getAddressDataModel(ArrayHelper::getValue($raw_address, 'ContactInformationType'));
                if (!(new AddressFullPackageBuilder($this->questionary, $address_data))
                    ->update($raw_address)) {
                    throw new UserException("Не удалось сохранить данные об адресе регистрации");
                }
            }
        }
    }

    protected function updatePassports($raw_data)
    {
        if (isset($raw_data['IdentificateDocuments'])) {
            if (!(new PassportsFullPackageBuilder($this->questionary, $this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setAllowDirectFetching($this->allow_direct_fetching)
                ->update($raw_data['IdentificateDocuments'])) {
                throw new UserException("Не удалось сохранить данные о паспортах");
            }
        }
    }

    protected function updatePhoto($raw_data)
    {
        if (isset($raw_data['AdditionalElement']) && $this->questionary) {
            $AdditionalElement = PersonalDataFullPackageBuilder::convertAdditionalElement($raw_data['AdditionalElement']);

            $avatarFileName = ArrayHelper::getValue($AdditionalElement, 'PhotoFileName');
            $avatarFileExt = ArrayHelper::getValue($AdditionalElement, 'PhotoFileExt');
            $avatarFileUid = ArrayHelper::getValue($AdditionalElement, 'PhotoFileUid');
            $avatarFileHash = ArrayHelper::getValue($AdditionalElement, 'PhotoFileHash');
            $avatarFileParts = ArrayHelper::getValue($AdditionalElement, 'PhotoFileParts');
            $avatarFilePartsCount = ArrayHelper::getValue($AdditionalElement, 'PhotoFilePartsCount');
            if ($avatarFileHash) {
                $avatarFileParts = ScansFullPackageBuilder::getPartsNames($avatarFileParts);
                
                $file_from_1c_object = (object)[
                    'FileName' => $avatarFileName,
                    'FileExt' => $avatarFileExt,
                    'FileUID' => $avatarFileUid,
                    'FileHash' => $avatarFileHash,
                    'FilePartsCount' => $avatarFilePartsCount,
                ];
                $receiving_file = (new ReceivingFile($file_from_1c_object));
                if ($avatarFileParts) {
                    $receiving_file->setTempFileNames($avatarFileParts);
                } else {
                    $receiving_file->fetchFile();
                }
                $this->questionary->attachPhoto($receiving_file);
            }
        }
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);

        if (empty($raw_data)) {
            return false;
        }
        $personal_data = $this->getPersonalDataForUpdate();

        $personal_data->setScenario(PersonalData::SCENARIO_GET_ANKETA);

        $personal_data->firstname = ArrayHelper::getValue($raw_data, 'Name');
        $personal_data->lastname = ArrayHelper::getValue($raw_data, 'Surname');
        $personal_data->middlename = ArrayHelper::getValue($raw_data, 'Patronymic');
        $personal_data->snils = ArrayHelper::getValue($raw_data, 'SNILS');

        $gender = ReferenceTypeManager::GetOrCreateReference(
            Gender::class,
            ArrayHelper::getValue($raw_data, 'GenderRef')
        );
        $personal_data->gender_id = ArrayHelper::getValue($gender, 'id');
        $personal_data->gender = ArrayHelper::getValue($gender, 'code');

        $personal_data->birthdate = ArrayHelper::getValue($raw_data, 'Birthday');
        $personal_data->birth_place = ArrayHelper::getValue($raw_data, 'Birthplace');

        $citizenship = ReferenceTypeManager::GetOrCreateReference(
            Country::class,
            ArrayHelper::getValue($raw_data, 'CitizenshipRef')
        );
        $personal_data->country_id = ArrayHelper::getValue($citizenship, 'id');
        if (isset($raw_data['Email'])) {
            $this->updateEmail($raw_data['Email']);
        }

        $personal_data->main_phone = ArrayHelper::getValue($raw_data, 'PhoneMobile');
        $personal_data->secondary_phone = ArrayHelper::getValue($raw_data, 'PhoneHome');

        $this->updateAddresses($raw_data);
        $this->updatePassports($raw_data);
        $this->updatePhoto($raw_data);
        $this->updateEntrantCodes($personal_data, $raw_data);
        $this->updateAdditionalElements($personal_data, $raw_data);

        $lang = ReferenceTypeManager::GetOrCreateReference(
            ForeignLanguage::class,
            ArrayHelper::getValue($raw_data, 'LanguageRef')
        );
        $personal_data->language_id = ArrayHelper::getValue($lang, 'id');
        $personal_data->language_code = ArrayHelper::getValue($lang, 'code');

        $personal_data->need_dormitory = (bool)$this->need_hostel;
        DraftsManager::SuspendHistory($personal_data);
        if (!$personal_data->save(false)) {
            throw new UserException(get_class($personal_data) . PHP_EOL . print_r($personal_data->errors, true));
        }

        $this->setExternalLinks($personal_data);
        return $this->questionary->user->save();
    }

    protected function updateEntrantCodes(PersonalData $personal_data, array $raw_data): void
    {
        if (isset($raw_data['EntrantUniqueCode'])) {
            $personal_data->entrant_unique_code = $raw_data['EntrantUniqueCode'];
        }
        if (isset($raw_data['EntrantUniqueCodeSpecialQuota'])) {
            $personal_data->entrant_unique_code_special_quota = $raw_data['EntrantUniqueCodeSpecialQuota'];
        }
    }

    protected function updateAdditionalElements(PersonalData $personal_data, array $raw_data): void
    {
    }

    protected function setExternalLinks(PersonalData $personal_data)
    {
        return; 
    }

    public function setNeedHostel($need_hostel)
    {
        $this->need_hostel = $need_hostel;
        return $this;
    }
}
