<?php

namespace common\modules\abiturient\models;

use common\components\AddressHelper\AddressHelper;
use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseApplicationPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\AttachmentManager;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\PageRelationManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\AbiturientAvatar;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\dictionary\Country;
use common\models\dictionary\DocumentType;
use common\models\EntityForDuplicatesFind;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\AttachmentsRelationPresenter;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\models\repositories\UserRegulationRepository;
use common\models\ToAssocCaster;
use common\models\traits\ArchiveTrait;
use common\models\traits\CheckAbiturientAccessibilityTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClassInput;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\bachelor\extensions\AbiturientQuestionaryFileAttacher;
use common\modules\abiturient\models\bachelor\ModerateHistory;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\interfaces\IQuestionnaireValidateModelInterface;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\modules\abiturient\models\repositories\FileRepository;
use DateTime;
use stdClass;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;



























class AbiturientQuestionary extends ActiveRecord
implements
    IQuestionnaireValidateModelInterface,
    ChangeLoggedModelInterface,
    IDraftable,
    ArchiveModelInterface,
    IHasRelations,
    ICanAttachFile
{
    use ArchiveTrait;
    use CheckAbiturientAccessibilityTrait;
    use HtmlPropsEncoder;

    const STATUS_CREATED = 0;
    const STATUS_SENT = 1;
    const STATUS_APPROVED = 2;
    const STATUS_NOT_APPROVED = 3;
    const STATUS_REJECTED_BY1C = 4;
    const STATUS_CREATE_FROM_1C = 5;

    


    public static function tableName()
    {
        return '{{%abiturient_questionary}}';
    }

    public $questionaryFileAttacher;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->questionaryFileAttacher = new AbiturientQuestionaryFileAttacher($this);
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'approver_id',
                    'status',
                    'draft_status'
                ],
                'integer'
            ],
            [
                ['have_no_previous_passport'],
                'boolean'
            ],
            [
                ['draft_status'],
                'in',
                'range' => [
                    self::DRAFT_STATUS_CREATED,
                    self::DRAFT_STATUS_SENT,
                    self::DRAFT_STATUS_MODERATING,
                    self::DRAFT_STATUS_APPROVED,
                ]
            ],
            [
                'draft_status',
                'default',
                'value' => self::DRAFT_STATUS_CREATED
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_CREATED
            ],
            [
                'have_no_previous_passport',
                'default',
                'value' => false
            ],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_CREATED,
                    self::STATUS_SENT,
                    self::STATUS_APPROVED,
                    self::STATUS_NOT_APPROVED,
                    self::STATUS_REJECTED_BY1C,
                    self::STATUS_CREATE_FROM_1C
                ]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'fio' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "fio" формы "Анкеты": `ФИО`'),
            'status' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "status" формы "Анкеты": `Статус`'),
            'user_id' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "user_id" формы "Анкеты": `Поступающий`'),
            'userRef' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "userRef" формы "Анкеты": `Код физ. лица`'), 
            'usermail' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "usermail" формы "Анкеты": `Email`'),
            'approver_id' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "approver_id" формы "Анкеты": `Проверивший модератор`'),
            'approved_at' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "approved_at" формы "Анкеты": `Проверено в`'),
            'created_at' => Yii::t('abiturient/bachelor/questionary/abiturient-questionary', 'Подпись для поля "created_at" формы "Анкеты": `Создано в`'),
        ];
    }

    public function getPersonalData()
    {
        return $this->hasOne(PersonalData::class, ['questionary_id' => 'id']);
    }

    


    public function getPassportData()
    {
        return $this->getRawPassportData()->andOnCondition([PassportData::tableName() . '.archive' => false]);
    }

    


    public function getRawPassportData()
    {
        return $this->hasMany(PassportData::class, ['questionary_id' => 'id']);
    }

    public function getAddressData()
    {
        return $this->hasOne(AddressData::class, ['questionary_id' => 'id']);
    }

    public function getActualAddressData()
    {
        return $this->hasOne(ActualAddressData::class, ['questionary_id' => 'id']);
    }

    public function getParentData()
    {
        return $this->hasMany(ParentData::class, ['questionary_id' => 'id'])
            ->andWhere([ParentData::tableName() . '.archive' => false]);
    }

    public function getAllParentData()
    {
        return $this->hasMany(ParentData::class, ['questionary_id' => 'id']);
    }

    


    public function getUser()
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['questionary_id' => 'id'])
            ->joinWith('attachmentType attachment_type')
            ->andWhere([
                Attachment::tableName() . '.deleted' => false,
                'attachment_type.hidden' => false,
                'attachment_type.system_type' => AttachmentType::SYSTEM_TYPE_COMMON,
                'attachment_type.id' => AttachmentType::find()->alias('at')->select('at.id')->notInRegulation()
            ])
            ->union(
                Attachment::find()
                    ->innerJoinWith('attachmentType attachment_type', false)
                    ->andWhere([
                        Attachment::tableName() . '.deleted' => false,
                        'attachment_type.related_entity' => AttachmentType::RELATED_ENTITY_REGISTRATION,
                        'attachment_type.id' => AttachmentType::find()->alias('at')->select('at.id')->notInRegulation()
                    ])
                    ->andWhere([
                        Attachment::tableName() . '.owner_id' => $this->user_id,
                        Attachment::tableName() . '.questionary_id' => null,
                    ])
            );
    }

    public function getPassportsAttachments()
    {
        $passport_attachments = $this->getPassportData()->innerJoinWith(['attachments attachment'])->select(['attachment.id']);
        return Attachment::find()->where(['id' => $passport_attachments])->with(['attachmentType']);
    }
    protected function buildAdditionalElements(): array
    {
        $result = [];
        $personal_data = $this->personalData;
        if ($personal_data) {
            $result[] = BaseApplicationPackageBuilder::buildAdditionalAttribute('EntrantUniqueCode', $personal_data->entrant_unique_code);
            $result[] = BaseApplicationPackageBuilder::buildAdditionalAttribute('EntrantUniqueCodeSpecialQuota', $personal_data->entrant_unique_code_special_quota);
        }
        return $result;
    }

    public function getEntrantRefFrom1C()
    {
        
        if (Yii::$app->configurationManager->fullPackageEntrantProfileAvailable()) {
            $result = $this->getRawGetEntrantProfilePackageFrom1C();
            if ($result && isset($result->Entrant) && isset($result->Entrant->EntrantRef)) {
                return $result->Entrant->EntrantRef;
            }
        }
        return null;
    }

    public function getRawGetEntrantProfilePackageFrom1C(): ?stdClass
    {
        if (!$this->user || !$this->user->userRef) {
            return null;
        }
        $result = Yii::$app->soapClientWebApplication->load_with_caching(
            'GetEntrantProfilePackage',
            [
                'EntrantRef' => UserReferenceTypeManager::GetProcessedUserReferenceType($this->user)
            ]
        );
        if ($result === false) {
            return null;
        }
        if (!isset($result->return) || !isset($result->return->Entrant)) {
            Yii::$app->session->setFlash(
                'errorGetAnketa',
                'Ошибка получения анкеты из ПК'
            );
            return null;
        }

        return $result->return;
    }

    public function getFrom1C($fetch_email = true, &$errors = null): bool
    {
        if (Yii::$app->configurationManager->fullPackageEntrantProfileAvailable()) {
            return $this->getFrom1CByGetEntrantProfilePackage($fetch_email, $errors);
        }
        return false;
    }

    public function getFrom1CWithParents($fetch_email = true, &$errors = null): bool
    {
        $this->getFrom1C($fetch_email, $errors);
        return true;
    }

    public function getFrom1CByGetEntrantProfilePackage($fetch_email = true, &$errors = null): bool
    {
        $raw_data = $this->getRawGetEntrantProfilePackageFrom1C();
        if (!$raw_data) {
            if ($errors !== null) {
                $label = Yii::t(
                    'abiturient/bachelor/questionary/abiturient-questionary',
                    'Подпись для блока ошибок в данных пользователя; формы "Анкеты": `Данные пользователя`'
                );
                $errors[$label] = 'Ошибка получения анкеты из ПК';
            }
            return false;
        }
        $this->save(false);

        return (new FullApplicationPackageBuilder(null))
            ->setQuestionary($this)
            ->updateUserRefByFullPackage($raw_data)
            ->updateQuestionary($raw_data, true, $fetch_email);
    }

    public function getEntireQuestionaryAttachmentCollections(): array
    {
        $collections = FileRepository::GetQuestionaryCollectionsFromTypes($this, [
            PageRelationManager::RELATED_ENTITY_QUESTIONARY,
            PageRelationManager::RELATED_ENTITY_REGISTRATION
        ]);

        $regulations = UserRegulationRepository::GetUserRegulationsWithFilesByQuestionaryAndRelatedEntity($this, [
            PageRelationManager::RELATED_ENTITY_QUESTIONARY,
            PageRelationManager::RELATED_ENTITY_REGISTRATION
        ]);

        $collections = array_merge($collections, ArrayHelper::getColumn($this->passportData, 'attachmentCollection'));
        return array_merge($collections, ArrayHelper::getColumn($regulations, 'attachmentCollection'));
    }

    public function isNotCreatedDraft()
    {
        if (in_array($this->draft_status, [IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_MODERATING, IDraftable::DRAFT_STATUS_APPROVED])) {
            return true;
        }
        
        if ($this->getLinkedBachelorApplication()
            ->andWhere([BachelorApplication::tableName() . '.draft_status' => [IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_MODERATING]])
            ->exists()
        ) {
            return true;
        }
        return false;
    }

    




    public function canEditQuestionary()
    {
        if (Yii::$app->user->identity->isModer()) {
            $linked_app = $this->getLinkedBachelorApplication()->one();
            if ($linked_app) {
                return $this->draft_status == $linked_app->getDraftStatusToModerate();
            }
            return $this->draft_status != IDraftable::DRAFT_STATUS_APPROVED;
        } else {
            return !$this->isNotCreatedDraft();
        }
    }

    public static function isBlockedAfterApprove(?AbiturientQuestionary $questionary): bool
    {
        if (is_null($questionary)) {
            return false;
        }

        $block_after_approve = !QuestionarySettings::getSettingByName('allow_edit_questionary_after_approve');
        return $block_after_approve
            && isset($questionary->user)
            && $questionary->hasApprovedApps();
    }

    public function getStatusMessage()
    {
        $first_app_type = ArrayHelper::getValue($this, 'user.applications.0.type');
        switch ($this->status) {
            case (self::STATUS_SENT):
                return Yii::$app->configurationManager->getText('questionary_sended', $first_app_type);

            case (self::STATUS_APPROVED):
                if (Yii::$app->configurationManager->sandboxEnabled) {
                    return Yii::$app->configurationManager->getText('questionary_approved_sandbox_on', $first_app_type);
                } else {
                    return Yii::$app->configurationManager->getText('questionary_approved_sandbox_off', $first_app_type);
                }

            case (self::STATUS_NOT_APPROVED):
                return Yii::$app->configurationManager->getText('questionary_notapproved', $first_app_type);

            case (self::STATUS_REJECTED_BY1C):
                return Yii::$app->configurationManager->getText('questionary_rejected_by1c', $first_app_type);

            case (self::STATUS_CREATE_FROM_1C):
                return Yii::$app->configurationManager->getText('questionary__create_from_1C', $first_app_type);

            default:
                return false;
        }
    }

    public function getFio()
    {
        return ArrayHelper::getValue($this, 'personalData.fio');
    }

    public function getUsermail()
    {
        return ArrayHelper::getValue($this, 'user.email');
    }

    public static function UpdateDataFromOneS(AbiturientQuestionary $questionary): AbiturientQuestionary
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $actual = DraftsManager::getActualQuestionary($questionary->user, true);

            if ($actual) {
                $raw_ref = $questionary->getEntrantRefFrom1C();
                if ($raw_ref) {
                    $questionary->user->assignUserRef($raw_ref);
                }
                if ($actual->id != $questionary->id) {
                    
                    $draft_status = $questionary->draft_status;
                    $status = $questionary->status;
                    if ($status == AbiturientQuestionary::STATUS_CREATED) {
                        $status = AbiturientQuestionary::STATUS_APPROVED;
                    }
                    


                    $questionary = DraftsManager::makeCopy($actual, $questionary);

                    $questionary->draft_status = $draft_status;
                    $questionary->status = $status;
                    $questionary->save(false);
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $questionary;
    }

    





    public function canCreateQuestionary()
    {
        $startDates = $this->getCampaignsStartDates();
        if (empty($startDates)) {
            return false;
        }
        foreach ($startDates as $start) {
            if ((new DateTime($start['minDate'])) < (new DateTime())) {
                return true;
            }
        }
        return false;
    }

    




    public function getCampaignsStartDates()
    {
        $applicationTypes = ApplicationType::findAll(['archive' => false]);
        if (empty($applicationTypes)) {
            return [];
        } else {
            $startDates = [];
            foreach ($applicationTypes as $applicationType) {
                $campaignInfo = (new Query())
                    ->select(["MIN(" . IndependentQueryManager::strToDateTime("date_start") . ") AS min_date_start"])
                    ->from('campaign_info')
                    ->where([
                        'archive' => false,
                        'campaign_id' => $applicationType->campaign_id
                    ])
                    ->one();

                $minDate = ArrayHelper::getValue($campaignInfo, 'min_date_start');
                if (isset($minDate)) {
                    $startDates[] = [
                        'minDate' => $minDate,
                        'nameCampaign' => $applicationType->name
                    ];
                }
            }
            return $startDates;
        }
    }

    


    public function getChangeHistory()
    {
        return $this->hasMany(
            ChangeHistory::class,
            ['questionary_id' => 'id']
        );
    }

    public function getChangeHistoryOrderedById()
    {
        return $this->getChangeHistory()->orderBy([ChangeHistory::tableName() . '.id' => SORT_ASC]);
    }

    private function processAddressDataFrom1C($response, $fieldName, $addressData)
    {
        return self::updateAddressFromOneS($addressData, $response->return->$fieldName);
    }

    public function updateAddressFromOneS(AddressData $addressData, $raw_data): ?AddressData
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            return null;
        }

        $addressData->postal_index = ArrayHelper::getValue($raw_data, 'PostalIndex');
        $russia_guid = Yii::$app->configurationManager->getCode('russia_guid');
        $country = null;
        $countryRef = ArrayHelper::getValue($raw_data, 'CountryRef');
        if (isset($countryRef) && !empty($countryRef)) {
            $country = ReferenceTypeManager::GetOrCreateReference(Country::class, $countryRef);
        }
        $countryCode = ArrayHelper::getValue($raw_data, 'Country');
        if (is_null($country) && isset($countryCode)) {
            $country = Country::findOne(['code' => (string)$countryCode, 'archive' => false]);
        }
        $addressData->country_id = $country->id ?? null;
        $addressData->not_found = false;
        $addressData->homeless = (bool)ArrayHelper::getValue($raw_data, 'Homeless', false);
        if (!empty($country) && $country->ref_key == $russia_guid) {
            $region = AddressHelper::getRegion(ArrayHelper::getValue($raw_data, 'Region'))->getOne();
            $area = AddressHelper::getArea($region, ArrayHelper::getValue($raw_data, 'Area'))->getOne();
            $city = AddressHelper::getCity($region, $area, ArrayHelper::getValue($raw_data, 'City'))->getOne();
            $town = AddressHelper::getTown($region, $area, $city, ArrayHelper::getValue($raw_data, 'Town'))->getOne();
            $street = AddressHelper::getStreet($region, $area, $city, $town, ArrayHelper::getValue($raw_data, 'Street'))->getOne();

            AddressData::setAddressProperty(
                $addressData,
                $region,
                ArrayHelper::getValue($raw_data, 'Region'),
                'region_id',
                'region_name',
                true,
                true
            );
            AddressData::setAddressProperty(
                $addressData,
                $area,
                ArrayHelper::getValue($raw_data, 'Area'),
                'area_id',
                'area_name'
            );
            AddressData::setAddressProperty(
                $addressData,
                $city,
                ArrayHelper::getValue($raw_data, 'City'),
                'city_id',
                'city_name'
            );
            AddressData::setAddressProperty(
                $addressData,
                $town,
                ArrayHelper::getValue($raw_data, 'Town'),
                'village_id',
                'town_name'
            );
            AddressData::setAddressProperty(
                $addressData,
                $street,
                ArrayHelper::getValue($raw_data, 'Street'),
                'street_id',
                'street_name'
            );

            $addressData->processKLADRCode();
        } else {
            $addressData->not_found = true;
            $addressData->region_name = ArrayHelper::getValue($raw_data, 'Region');
            $addressData->area_name = ArrayHelper::getValue($raw_data, 'Area');
            $addressData->city_name = ArrayHelper::getValue($raw_data, 'City');
            $addressData->town_name = ArrayHelper::getValue($raw_data, 'Town');
            $addressData->street_name = ArrayHelper::getValue($raw_data, 'Street');
        }
        $addressData->house_number = ArrayHelper::getValue($raw_data, 'House');
        $addressData->housing_number = ArrayHelper::getValue($raw_data, 'Korpus');
        $addressData->flat_number = ArrayHelper::getValue($raw_data, 'Kvartira');
        $addressData->isFrom1C = true;
        $addressData->cleanUnusedAttributes();
        DraftsManager::SuspendHistory($addressData);

        if (!$addressData->save()) {
            throw new UserException("Не удалось обновить адрес: " . print_r($addressData->errors, true));
        }
        return $addressData;
    }

    public function getOrCreateActualAddressData(bool $run_model_preparation = true)
    {
        $addressData = $this->actualAddressData;
        if ($addressData === null) {
            $addressData = new ActualAddressData();
            $addressData->questionary_id = $this->id;
            $addressData->homeless = false;
            $country = Country::findOne([
                'ref_key' => Yii::$app->configurationManager->getCode('russia_guid'),
                'archive' => false
            ]);
            if ($country === null) {
                throw new UserException('Невозможно найти страну по коду по умолчанию.');
            }
            $addressData->country_id = $country->id;
        }
        if ($run_model_preparation) {
            $addressData->validation_extender ? $addressData->validation_extender->modelPreparationCallback() : null;
        }
        return $addressData;
    }

    public function getAbiturientAvatar()
    {
        return $this->hasOne(AbiturientAvatar::class, [
            'questionary_id' => 'id'
        ])->andWhere([
            'attachment_type_id' => AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_ABITURIENT_AVATAR)->id,
            'deleted' => false
        ]);
    }

    







    public function getComputedAbiturientAvatar(): AbiturientAvatar
    {
        $avatar = $this->abiturientAvatar;
        if ($avatar === null) {
            $avatar = new AbiturientAvatar();
            $avatar->owner_id = $this->user->id;
            $avatar->questionary_id = $this->id;
            $avatar->deleted = false;
            $avatar->attachment_type_id = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_ABITURIENT_AVATAR)->id;
        }
        return $avatar;
    }

    public function attachPhoto(IReceivedFile $receivingFile): ?File
    {
        $attachment = $this->getComputedAbiturientAvatar();
        if ($attachment->getIsNewRecord()) {
            $attachment->save(false);
        }
        $file = $receivingFile->getFile($attachment);
        $attachment->LinkFile($file);
        return $file;
    }

    public function beforeDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $deleteSuccess = true;
        try {

            $errorFrom = '';
            $personalData = $this->personalData;
            if (isset($personalData)) {
                $deleteSuccess = $personalData->delete();
            }
            if (!$deleteSuccess) {
                $errorFrom .= "{$this->tableName()} -> {$personalData->tableName()} -> {$personalData->id}\n";
            }
            if ($deleteSuccess) {
                $passportData = $this->rawPassportData;
                if (!empty($passportData)) {
                    foreach ($passportData as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $addressData = $this->addressData;
                if (isset($addressData)) {
                    $deleteSuccess = $addressData->delete();
                }
                if (!$deleteSuccess) {
                    $errorFrom .= "{$this->tableName()} -> {$addressData->tableName()} -> {$addressData->id}\n";
                }
            }
            if ($deleteSuccess) {
                $parentData = $this->allParentData;
                if (!empty($parentData)) {
                    foreach ($parentData as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $actualAddressData = $this->actualAddressData;
                if (isset($actualAddressData)) {
                    $deleteSuccess = $actualAddressData->delete();
                }
                if (!$deleteSuccess) {
                    $errorFrom .= "{$this->tableName()} -> {$actualAddressData->tableName()} -> {$actualAddressData->id}\n";
                }
            }
            if ($deleteSuccess) {
                $attachments = $this->attachments;
                if (!empty($attachments)) {
                    foreach ($attachments as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $userRegulations = $this->userRegulations;
                if (!empty($userRegulations)) {
                    foreach ($userRegulations as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $change_history_ids = $this->getChangeHistory()->select('id')->column();
                $change_history_class_ids = ChangeHistoryEntityClass::find()->where(['change_id' => $change_history_ids])->select('id')->column();
                $change_history_class_input_ids = ChangeHistoryEntityClassInput::find()->where(['entity_class_id' => $change_history_class_ids])->select('id')->column();
                ChangeHistoryEntityClassInput::deleteAll(['id' => $change_history_class_input_ids]);
                ChangeHistoryEntityClass::deleteAll(['id' => $change_history_class_ids]);
                ChangeHistory::deleteAll(['id' => $change_history_ids]);
            }

            if ($deleteSuccess) {
                $transaction->commit();
            } else {
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
                $transaction->rollBack();
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при удалении данных с портала. " . $e->getMessage());
            return false;
        }
        return $deleteSuccess;
    }

    public function getValidatedName(): string
    {
        return Yii::t(
            'abiturient/bachelor/questionary/abiturient-questionary',
            'Валидационное имя; формы "Анкеты": `Анкета`'
        );
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ABITURIENT_QUESTIONARY;
    }

    public function getChangeLoggedAttributes()
    {
        return ['userRef' => function (AbiturientQuestionary $model) {
            return ArrayHelper::getValue($model, 'user.userRef.reference_id');
        }];
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT;
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->user->getFullName();
    }

    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        return null;
    }

    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler)
    {
        return null;
    }

    public function getLinkedBachelorApplication(): ActiveQuery
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id'])
            ->viaTable(
                '{{%application_and_questionary_junction}}',
                ['questionary_id' => 'id']
            );
    }

    public function getUserRegulations(): ActiveQuery
    {
        return $this->hasMany(UserRegulation::class, ['abiturient_questionary_id' => 'id']);
    }

    public function isNotFilled()
    {
        return $this->draft_status == IDraftable::DRAFT_STATUS_CREATED && $this->status == AbiturientQuestionary::STATUS_CREATED;
    }

    public function getNotFilledRequiredCommonAttachmentTypeIds(): array
    {
        return Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds([AttachmentType::RELATED_ENTITY_REGISTRATION, AttachmentType::RELATED_ENTITY_QUESTIONARY])
        );
    }

    public function getSpecificEntitiesFilesInfo(): array
    {
        return [
            ...$this->getPassportFilesInfo(),
        ];
    }

    public function getPassportFilesInfo(): array
    {
        $files = [];
        $passports = $this->passportData;

        foreach ($passports as $passport) {
            $files = [...$files, ...$passport->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function isRequiredCommonFilesAttached(): bool
    {
        return !$this->getNotFilledRequiredCommonAttachmentTypeIds();
    }

    public function getOnePassportWithoutFile(): ?PassportData
    {
        
        $passports_with_empty_files = AttachmentManager::GetEntityWithEmptyFilesQuery(PassportData::instance())
            ->andWhere([PassportData::tableName() . '.questionary_id' => $this->id])
            ->all();
        foreach ($passports_with_empty_files as $passport) {
            if ($passport->getAttachmentCollection()->isRequired()) {
                return $passport;
            }
        }

        return null;
    }

    public function isPassportsRequiredFilesAttached(): bool
    {
        return is_null($this->onePassportWithoutFile);
    }

    public function isPreviousPassportsFilled(): bool
    {
        if ($this->have_no_previous_passport) {
            return true;
        }
        $passports = $this->passportData;
        if (count($passports) == 1) {
            $age_with_penalty_in_days = $this->personalData->age * 365 - 100;
            
            if ($age_with_penalty_in_days > 365 * 20) {
                return false;
            }

            $passport = $passports[0];
            if ($passport->isNotRegularIssueDate()) {
                return false;
            }
        }
        return true;
    }

    public function translateDraftStatus(): string
    {
        switch ($this->draft_status) {
            case IDraftable::DRAFT_STATUS_CREATED:
                return Yii::$app->configurationManager->getText('draft_status_questionary_preparing');

            case IDraftable::DRAFT_STATUS_SENT:
                return Yii::$app->configurationManager->getText('draft_status_questionary_sent');

            case IDraftable::DRAFT_STATUS_MODERATING:
                return Yii::$app->configurationManager->getText('draft_status_questionary_moderating');

            case IDraftable::DRAFT_STATUS_APPROVED:
                return Yii::$app->configurationManager->getText('draft_status_questionary_clean_copy');
        }
        throw new UserException("Не известный статус черновика");
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToOneRelationPresenter('personalData', [
                'parent_instance' => $this,
                'child_class' => PersonalData::class,
                'child_column_name' => 'questionary_id',
            ]),
            new OneToManyRelationPresenter('passportData', [
                'parent_instance' => $this,
                'child_class' => PassportData::class,
                'child_column_name' => 'questionary_id',
            ]),
            new OneToOneRelationPresenter('addressData', [
                'parent_instance' => $this,
                'child_class' => AddressData::class,
                'child_column_name' => 'questionary_id',
            ]),
            new OneToOneRelationPresenter('actualAddressData', [
                'parent_instance' => $this,
                'child_class' => ActualAddressData::class,
                'child_column_name' => 'questionary_id',
            ]),
            new OneToManyRelationPresenter('parentData', [
                'parent_instance' => $this,
                'child_class' => ParentData::class,
                'child_column_name' => 'questionary_id',
            ]),
            
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
            new OneToManyRelationPresenter('changeHistory', [
                'parent_instance' => $this,
                'child_class' => ChangeHistory::class,
                'child_column_name' => 'questionary_id',
                'actual_relation_name' => 'changeHistoryOrderedById',
                'ignore_in_comparison' => true,
                'find_exists_child' => false,
                'make_new_child' => true,
            ]),
            new OneToManyRelationPresenter('user_regulations', [
                'parent_instance' => $this,
                'child_class' => UserRegulation::class,
                'child_column_name' => 'abiturient_questionary_id',
                'actual_relation_name' => 'userRegulations',
            ])
        ];
    }

    




    public function hasApprovedAppsQuery(): ActiveQuery
    {
        $tnModerateHistory = ModerateHistory::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $tnAbiturientQuestionary = AbiturientQuestionary::tableName();

        
        
        $approvedAppsUserIdQuery = BachelorApplication::find()
            ->active()
            ->select("{$tnBachelorApplication}.user_id")
            ->joinWith('moderateHistory')
            ->andWhere(['IN', "{$tnBachelorApplication}.user_id", $this->user_id])
            ->andWhere([
                'or',
                ["{$tnModerateHistory}.status" => BachelorApplication::STATUS_APPROVED],
                ["{$tnBachelorApplication}.status" => BachelorApplication::STATUS_APPROVED],
            ]);

        
        
        return AbiturientQuestionary::find()
            ->active()
            ->andWhere(['NOT IN', "{$tnAbiturientQuestionary}.id", [null, 0]])
            ->andWhere(['IN', "{$tnAbiturientQuestionary}.user_id", $approvedAppsUserIdQuery])
            ->andWhere(['=', "{$tnAbiturientQuestionary}.draft_status", IDraftable::DRAFT_STATUS_APPROVED]);
    }

    




    public function hasApprovedApps(): bool
    {
        return $this->hasApprovedAppsQuery()->exists();
    }

    






    public function hasPassedApplicationWithEditableAttachments(): bool
    {
        $tnAttachmentType = AttachmentType::tableName();
        $attachmentQurtyArray = [
            'and',
            ["{$tnAttachmentType}.hidden" => false],
            [
                'IN',
                "{$tnAttachmentType}.related_entity",
                [
                    AttachmentType::RELATED_ENTITY_QUESTIONARY,
                    AttachmentType::RELATED_ENTITY_REGISTRATION,
                ]
            ],
            [
                'or',
                ["{$tnAttachmentType}.allow_delete_file_after_app_approve" => true],
                ["{$tnAttachmentType}.allow_add_new_file_after_app_approve" => true],
            ],
        ];

        return AttachmentType::find()
            ->andWhere($attachmentQurtyArray)
            ->andWhere([
                'IS NOT',
                $this
                    ->hasApprovedAppsQuery()
                    ->select('id')
                    ->limit(1),
                null
            ])
            ->andWhere(["{$tnAttachmentType}.admission_campaign_ref_id" => null])
            ->exists();
    }

    public function getEntityForDuplicatesFind(): EntityForDuplicatesFind
    {
        $passports = ArrayHelper::getValue($this, 'passportData', []);
        $passport_data = [];
        foreach ($passports as $passport) {
            $passport_data[] = [
                'type' => $passport->documentType,
                'number' => (string)$passport->number,
                'series' => (string)$passport->series,
            ];
        }
        return new EntityForDuplicatesFind(
            (string)ArrayHelper::getValue($this, 'personalData.firstname'),
            (string)ArrayHelper::getValue($this, 'personalData.lastname'),
            (string)ArrayHelper::getValue($this, 'personalData.middlename'),
            (string)ArrayHelper::getValue($this, 'personalData.formated_birthdate'),
            (string)ArrayHelper::getValue($this, 'personalData.snils'),
            $passport_data
        );
    }

    public function attachFile(IReceivedFile $receivingFile, DocumentType $documentType): ?File
    {
        $attachmentTypeIds = AttachmentType::find()
            ->joinWith(['documentType document_type'])
            ->andWhere([
                'document_type.ref_key' => $documentType->ref_key,
                'attachment_type.hidden' => false,
            ])
            ->select(['attachment_type.id'])
            ->column();
        if (!$attachmentTypeIds) {
            return null;
        }
        $file = null;
        $file = $this->questionaryFileAttacher->attachFileToQuestionaryAttachments($receivingFile, $attachmentTypeIds, $file);
        $file = $this->questionaryFileAttacher->attachFileToQuestionaryRegulations($receivingFile, $attachmentTypeIds, $file);
        return $this->questionaryFileAttacher->attachFileToUserRegulations($receivingFile, $attachmentTypeIds, $file);
    }

    public function removeNotPassedFiles(array $file_ids_to_ignore)
    {
        $questionary = $this;
        $ignored_questionary_attachment_ids = Attachment::find()
            ->select(['MAX(attachment.id) id'])
            ->joinWith('attachmentType')
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['attachment.id' => (new Query())->from(['a' => $questionary->getAttachments()])->select('a.id')])
            ->andWhere(['linked_file.id' => $file_ids_to_ignore])
            ->groupBy(['linked_file.id', 'attachment_type.id']);

        $attachments_to_delete = $questionary->getAttachments()
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['NOT', ['attachment.id' => $ignored_questionary_attachment_ids]])
            ->all();

        
        foreach ($attachments_to_delete as $attachment_to_delete) {
            $attachment_to_delete->silenceSafeDelete();
        }
    }

    public function getAttachedFilesInfo(): array
    {
        return $this->getAdditionalAttachmentsInfo();
    }

    public function getAdditionalAttachmentsInfo(): array
    {
        $files = [];

        $attachments = $this->attachments;
        foreach ($attachments as $attachment) {
            $files[] = [
                $attachment,
                ArrayHelper::getValue($attachment, 'attachmentType.documentType'),
                ArrayHelper::getValue($attachment, 'attachmentType.name')
            ];
        }
        return $files;
    }
}
