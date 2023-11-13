<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ParentDataBuilders\ParentsFullPackageBuilder;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\AllAgreements\AllAgreementsHandler;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\bachelor\OrderHandler;
use common\modules\abiturient\models\services\FullPackageFilesSyncer;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class FullApplicationPackageBuilder extends BaseApplicationPackageBuilder
{
    protected $questionary;
    protected $update_sent_at = false;

    public function __construct(?BachelorApplication $app)
    {
        parent::__construct($app);
        $this->setFilesSyncer(new FullPackageFilesSyncer($this->application));
        if ($app && $app->abiturientQuestionary) {
            $this->setQuestionary($app->abiturientQuestionary);
        }
    }

    public function setUpdateSentAt(bool $update_sent_at): FullApplicationPackageBuilder
    {
        $this->update_sent_at = $update_sent_at;
        return $this;
    }

    public function setQuestionary(AbiturientQuestionary $questionary): FullApplicationPackageBuilder
    {
        $this->questionary = $questionary;
        $this->files_syncer->setQuestionary($questionary);
        return $this;
    }

    public function build()
    {
        return $this->buildFullPackage();
    }

    public function buildFullPackage()
    {
        $result = [
            'Entrant' => $this->application->buildEntrantArray(),
            'PersonalData' => $this->buildPersonalDataForFullPackage(),
        ];
        $relatives = $this->buildRelativesDataForFullPackage();
        if ($relatives) {
            $result['Relatives'] = $relatives;
        }
        $result['NeedHostel'] = ArrayHelper::getValue($this->getAbiturientQuestionary(), 'personalData.need_dormitory', false);

        
        $apps_with_benefits = Yii::createObject(ApplicationsAndPreferencesFullPackageBuilder::class, [$this->application])
            ->setFilesSyncer($this->files_syncer)
            ->setSpecialitiesFiltrationCallback($this->getSpecialitiesFiltrationCallback())
            ->build();
        $result['Applications'] = $apps_with_benefits['Applications'];

        $EntranceTestsResults = (new EntranceTestsResultsFullPackageBuilder($this->application))
            ->build();
        if (!empty($EntranceTestsResults)) {
            $result['EntranceTestsResults'] = $EntranceTestsResults;
        }


        $Achievements = Yii::createObject(AchievementsFullPackageBuilder::class, [$this->application])
            ->setFilesSyncer($this->files_syncer)
            ->build();
        if (!empty($Achievements)) {
            $result['Achievements'] = $Achievements;
        }

        
        $result['EducationDocuments'] = $apps_with_benefits['EducationDocuments'];

        if (!empty($apps_with_benefits['Benefits'])) {
            $result['Benefits'] = $apps_with_benefits['Benefits'];
        }
        if (!empty($apps_with_benefits['Targets'])) {
            $result['Targets'] = $apps_with_benefits['Targets'];
        }
        if (!empty($apps_with_benefits['Olympics'])) {
            $result['Olympics'] = $apps_with_benefits['Olympics'];
        }
        $result['ApplicationDate'] = str_replace(" ", "T", date("Y-m-d H:i:s", $this->application->sent_at));

        $AdditionalFiles = (new ScansFullPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->setFileLinkableEntity($this->application)
            ->build();
        if ($AdditionalFiles) {
            $result['AdditionalFiles'] = $AdditionalFiles;
        }
        return $result;
    }

    public function buildPersonalDataForFullPackage()
    {
        return (new PersonalDataFullPackageBuilder($this->getAbiturientQuestionary(), $this->application))
            ->setFilesSyncer($this->files_syncer)
            ->build();
    }

    public function buildRelativesDataForFullPackage()
    {
        return (new ParentsFullPackageBuilder($this->getAbiturientQuestionary(), $this->application))
            ->setFilesSyncer($this->files_syncer)
            ->build();
    }

    public function updateUserRefByFullPackage($raw_data)
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        $user = $this->application->user ?? $this->questionary->user;
        if ($user) {
            $user->assignUserRef(ArrayHelper::getValue($raw_data, 'Entrant.EntrantRef'));
        }
        return $this;
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $application_date = (string)$raw_data['ApplicationDate'];
            if ($application_date === BaseApplicationPackageBuilder::EMPTY_DATE) {
                throw new UserException("В Информационной системе вуза не обнаружена информация о заявлении");
            }
            $application_date_timestamp = strtotime(
                str_replace('T', ' ', $application_date)
            );
            $this->application->sent_at = $application_date_timestamp;
            if (!$this->application->approved_at) {
                $this->application->approved_at = $this->application->sent_at;
            }
            $this->application->setupSyncData();

            $this->application->save(true, ['approved_at', 'synced_with_1C_at']);

            $this->updateQuestionary($raw_data, false);

            $state = Yii::createObject(ApplicationsAndPreferencesFullPackageBuilder::class, [$this->application])
                ->setFilesSyncer($this->files_syncer)
                ->setRawBenefits(
                    ArrayHelper::getValue($raw_data, 'Benefits', []),
                    ArrayHelper::getValue($raw_data, 'Olympics', []),
                    ArrayHelper::getValue($raw_data, 'Targets', [])
                )
                ->setRawEducations(ArrayHelper::getValue($raw_data, 'EducationDocuments', []))
                ->update(ArrayHelper::getValue($raw_data, 'Applications'));

            if (!$state) {
                throw new UserException("Не удалось обновить данные из Информационной системы вуза в блоке направлений подготовки");
            }
            $state = (new EntranceTestsResultsFullPackageBuilder($this->application))
                ->update(ArrayHelper::getValue($raw_data, 'EntranceTestsResults'));
            if (!$state) {
                throw new UserException("Не удалось обновить данные из Информационной системы вуза в блоке ВИ");
            }
            $state = Yii::createObject(AchievementsFullPackageBuilder::class, [$this->application])
                ->setFilesSyncer($this->files_syncer)
                ->update(ArrayHelper::getValue($raw_data, 'Achievements'));
            if (!$state) {
                throw new UserException("Не удалось обновить данные из Информационной системы вуза в блоке индивидуальных достижений");
            }
            $linkable_entity = $this->application;
            $this->updateAdditionalFiles($raw_data, $linkable_entity);

            AllAgreementsHandler::ProcessAllAgreements($this->application, ArrayHelper::getValue($raw_data, 'AllAgreements'));
            AllAgreementsHandler::UpdateConsentDates($this->application);

            foreach ($this->application->specialities as $bachelor_speciality) {
                $contracts = ArrayHelper::getValue($raw_data, 'Contracts', []);
                if ($contracts) {
                    $bachelor_speciality->buildAndUpdateContractRefFor1C($contracts);
                }
                if ($this->update_sent_at) {
                    $bachelor_speciality->updateSentAt($application_date_timestamp);
                }
            }

            OrderHandler::ProcessOrder($this->application, ArrayHelper::getValue($raw_data, 'Order'));

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка обновления заявления: {$e->getMessage()}");
            $this->files_syncer->ClearReceivedFiles();
            throw $e;
        }
        return true;
    }

    protected function getAbiturientQuestionary(): ?AbiturientQuestionary
    {
        if ($this->questionary) {
            return $this->questionary;
        }
        return $this->application->abiturientQuestionary ?? null;
    }

    public function updateQuestionary($raw_data, bool $update_files, bool $fetch_email = false): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $state = (new PersonalDataFullPackageBuilder($this->getAbiturientQuestionary(), $this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setAllowDirectFetching($update_files)
                ->setFetchEmail($fetch_email)
                ->setNeedHostel(ArrayHelper::getValue($raw_data, 'NeedHostel', false))
                ->update(ArrayHelper::getValue($raw_data, 'PersonalData'));

            if (!$state) {
                throw new UserException("Не удалось обновить данные из Информационной системы вуза в блоке персональных данных");
            }

            $state = (new ParentsFullPackageBuilder($this->getAbiturientQuestionary(), $this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setAllowDirectFetching($update_files)
                ->update(ArrayHelper::getValue($raw_data, 'Relatives'));
            if (!$state) {
                throw new UserException("Не удалось обновить данные из Информационной системы вуза в блоке родителей или законных представителей");
            }

            if ($update_files) {
                $linkable_entity = $this->getAbiturientQuestionary();
                $this->updateAdditionalFiles($raw_data, $linkable_entity, true);
            }

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка обновления анкеты: {$e->getMessage()}");
            $this->files_syncer->ClearReceivedFiles();
            throw $e;
        }
    }

    protected function updateAdditionalFiles(array $raw_data, ICanAttachFile $linkable_entity, bool $allow_direct_fetching = false)
    {
        $additional_files = ArrayHelper::getValue($raw_data, 'AdditionalFiles', []);
        if (!is_array($additional_files) || ArrayHelper::isAssociative($additional_files)) {
            $additional_files = [$additional_files];
        }
        $all_files = array_merge($additional_files, $this->files_syncer->getProcessedRawFiles());
        
        $all_files = array_values(array_map("unserialize", array_unique(array_map("serialize", $all_files))));

        (new ScansFullPackageBuilder($this->application))
            ->setFilesSyncer($this->files_syncer)
            ->setFileLinkableEntity($linkable_entity)
            ->setAllowDirectFetching($allow_direct_fetching)
            ->update($all_files);

        $this->files_syncer->ClearReceivedFiles();
    }

    public function sendFiles(): FullApplicationPackageBuilder
    {
        $this->files_syncer->SendFiles();
        return $this;
    }

    public function receiveFiles(): FullApplicationPackageBuilder
    {
        $this->files_syncer->FetchMissingFiles();
        return $this;
    }
}
