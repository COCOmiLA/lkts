<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\queries\ArchiveQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\PersonalData;
use yii\helpers\ArrayHelper;

class PassportsFullPackageBuilder extends BaseQuestionaryPackageBuilder
{
    
    protected $application;

    public function __construct(AbiturientQuestionary $questionary, ?BachelorApplication $application = null)
    {
        parent::__construct($questionary);
        $this->application = $application;
    }

    protected bool $allow_direct_fetching = false;

    public function setAllowDirectFetching(bool $allow_direct_fetching): PassportsFullPackageBuilder
    {
        $this->allow_direct_fetching = $allow_direct_fetching;
        return $this;
    }

    public function build()
    {
        $personal_data = $this->getPersonalData();

        $passports = [];
        $passports_data = $this->getPassportData(true);
        if ($this->application) {
            $approvedAt = $this->application->approved_at;
            if ($approvedAt) {
                $passports_data = $passports_data->onlyRecentlyRemovedAndActualRecords($approvedAt);
            }
        }
        $passports_data = $passports_data
            ->with(['documentType'])
            ->sortByArchiveFlag()
            ->all();
        if (!$passports_data) {
            $passports_data = [new PassportData()];
        }
        foreach ($passports_data as $passport) {
            
            $is_removed = $passport->isArchive();
            $tmp = [
                'Name' => $personal_data->firstname,
                'Surname' => $personal_data->lastname,
                'Patronymic' => $personal_data->middlename,
                'Document' => [
                    'DocumentTypeRef' => ReferenceTypeManager::GetReference($passport, 'documentType'),
                    'DocSeries' => $passport->series,
                    'DocNumber' => $passport->number,
                    'DocOrganization' => (new ContractorPackageBuilder(null, $passport->contractor))->build(),
                    'IssueDate' => (string)$passport->formatted_issued_date,
                    'DocumentCheckStatusRef' => $passport->buildDocumentCheckStatusRefType(),
                    'ReadOnly' => $passport->read_only ? 1 : 0,
                ],
                'SubdivisionCode' => $passport->department_code,
                'Removed' => $is_removed ? 1 : 0,
            ];
            if (!$is_removed && $passport->attachments) {
                $tmp['Files'] = (new ScansFullPackageBuilder($this->application))
                    ->setFilesSyncer($this->files_syncer)
                    ->setFileLinkableEntity($passport)
                    ->build();
            }
            $passports[] = $tmp;
        }

        return $passports;
    }

    public function getPassportData($all = false): ArchiveQuery
    {
        if ($all) {
            return $this->questionary->getRawPassportData();
        }

        return $this->questionary->getPassportData();
    }

    public function getPersonalData(): PersonalData
    {
        return $this->questionary->personalData;
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            $raw_data = [];
        }
        if (!is_array($raw_data) || ArrayHelper::isAssociative($raw_data)) {
            $raw_data = [$raw_data];
        }
        $touched_ids = [];
        $questionary = $this->questionary;
        foreach ($raw_data as $raw_passport) {
            if (
                empty($raw_passport)
                || empty(ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'))
                || ReferenceTypeManager::isReferenceTypeEmpty(ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'))
            ) {
                continue;
            }
            $local_pass = null;
            $local_pass = PassportData::GetOrCreateFromRaw(
                $questionary,
                ArrayHelper::getValue($raw_passport, 'Document.DocSeries'),
                ArrayHelper::getValue($raw_passport, 'Document.DocNumber'),
                ArrayHelper::getValue($raw_passport, 'Document.IssueDate'),
                ArrayHelper::getValue($raw_passport, 'Document.DocOrganization'),
                ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'),
                ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_passport, 'Document.DocumentCheckStatusRef', [])),
                (bool) ArrayHelper::getValue($raw_passport, 'Document.ReadOnly', false),
                $touched_ids
            );

            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($local_pass)
                ->setAllowDirectFetching($this->allow_direct_fetching)
                ->update($raw_passport['Files'] ?? []);
            $touched_ids[] = $local_pass->id;
        }
        $passports_to_delete = PassportData::find()->where(['and', ['questionary_id' => $questionary->id], ['not in', 'id', $touched_ids]])->all();
        foreach ($passports_to_delete as $passport) {
            $passport->delete();
        }

        return true;
    }
}
