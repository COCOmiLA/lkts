<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\UUIDManager;
use common\models\EmptyCheck;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\EducationData;
use yii\helpers\ArrayHelper;

class EducationFullPackageBuilder extends BaseApplicationPackageBuilder
{
    public function build()
    {
        $result = [];
        $educations_themselves = [];
        $approvedAt = $this->application->approved_at;
        $educations = $this->application->getRawEducations();
        if ($approvedAt) {
            $educations = $educations->onlyRecentlyRemovedAndActualRecords($approvedAt);
        }
        $educations = $educations
            ->sortByArchiveFlag()
            ->all();
        foreach ($educations as $education) {
            if (EmptyCheck::isEmpty($education->tmp_uuid)) {
                $education->tmp_uuid = UUIDManager::GetUUID();
            }
            $educations_themselves[] = $education;
            $result[] = $this->buildEducation($education);
        }
        return [$educations_themselves, $result];
    }

    protected function buildEducation(EducationData $education): array
    {
        $tmp = EducationData::build1sStructure($education);
        $is_removed = $education->isArchive();
        $tmp['Removed'] = $is_removed ? 1 : 0;
        if (!$is_removed && $education->attachments) {
            $tmp['Files'] = (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($education)
                ->build();
        }
        return $tmp;
    }

    public function update($raw_data): array
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            $raw_data = [];
        }
        if (!is_array($raw_data) || ArrayHelper::isAssociative($raw_data)) {
            $raw_data = [$raw_data];
        }
        $touched_education_ids = [];
        $processed_educations = [];
        foreach ($raw_data as $raw_education) {
            $edu_data = EducationData::GetOrCreateFromRaw(
                ArrayHelper::getValue($raw_education, 'Document.DocSeries'),
                ArrayHelper::getValue($raw_education, 'Document.DocNumber'),
                ArrayHelper::getValue($raw_education, 'Document.DocOrganization'),
                ArrayHelper::getValue($raw_education, 'Document.IssueDate'),
                ArrayHelper::getValue($raw_education, 'GraduationYear'),
                ArrayHelper::getValue($raw_education, 'EducationDocumentReferenceType'),
                ArrayHelper::getValue($raw_education, 'Document.DocumentTypeRef'),
                ArrayHelper::getValue($raw_education, 'EducationTypeRef'),
                ArrayHelper::getValue($raw_education, 'ProfileRef'),
                ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_education, 'Document.DocumentCheckStatusRef', [])),
                (bool) ArrayHelper::getValue($raw_education, 'Document.ReadOnly', false),
                $this->application
            );

            $edu_data->tmp_uuid = ArrayHelper::getValue($raw_education, 'EducationDocumentTempGUID');
            $edu_data->original_from_epgu = (bool) ArrayHelper::getValue($raw_education, 'OriginalFromEPGU', false);

            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($edu_data)
                ->update($raw_education['Files'] ?? []);

            $processed_educations[] = $edu_data;
            $touched_education_ids[] = $edu_data->id;
        }
        $touched_education_ids = array_values(array_unique($touched_education_ids));
        foreach ($this->application->getEducations()
            ->andWhere(['not', [EducationData::tableName() . '.id' => $touched_education_ids]])
            ->all() as $edu_to_delete) {
            $edu_to_delete->archive();
        }
        return $processed_educations;
    }
}
