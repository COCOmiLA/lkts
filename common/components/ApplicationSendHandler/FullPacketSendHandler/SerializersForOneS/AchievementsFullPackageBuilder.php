<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\IndividualAchievement;
use common\services\NamesManagementService;
use yii\helpers\ArrayHelper;

class AchievementsFullPackageBuilder extends BaseApplicationPackageBuilder
{
    protected NamesManagementService $namesManagementService;

    public function __construct(?BachelorApplication $app, NamesManagementService $namesManagementService)
    {
        parent::__construct($app);
        $this->namesManagementService = $namesManagementService;
    }

    public function build()
    {
        $result = [];
        $ias = $this->application
            ->getRawIndividualAchievements()
            ->andWhere(['not', ['status' => IndividualAchievement::STATUS_TO_DELETE]])
            ->onlyRecentlyRemovedAndActualRecords($this->application->approved_at)
            ->sortByArchiveFlag()
            ->all();
        foreach ($ias as $ia) {
            $result[] = $this->buildIA($ia);
        }
        return $result;
    }

    public function buildIA(IndividualAchievement $achievement)
    {
        $is_removed = $achievement->isArchive();
        $result = [
            'AchievementCategoryRef' => ReferenceTypeManager::GetReference($achievement, 'achievementType'),
            'Document' => [
                'DocumentTypeRef' => ReferenceTypeManager::GetReference($achievement, 'documentType'),
                'DocSeries' => $achievement->document_series,
                'DocNumber' => $achievement->document_number,
                'DocOrganization' => (new ContractorPackageBuilder(null, $achievement->contractor))->build(),
                'IssueDate' => $achievement->formated_document_date,
                'DocumentCheckStatusRef' => $achievement->buildDocumentCheckStatusRefType(),
                'ReadOnly' => $achievement->read_only ? 1 : 0,
            ],
            $this->namesManagementService->getFullPackageAchievementCommentColumnName() => $achievement->additional,
            'Removed' => $is_removed ? 1 : 0,
        ];
        if (!$is_removed) {
            $files = (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($achievement)
                ->build();
            if ($files) {
                $result['Files'] = $files;
            }
        }
        return $result;
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
        $ias_query = $this->application->getIndividualAchievements();
        foreach ($raw_data as $raw_achievement) {
            $ind_arch = IndividualAchievement::GetOrCreateFromRaw(
                ArrayHelper::getValue($raw_achievement, 'Document.DocSeries'),
                ArrayHelper::getValue($raw_achievement, 'Document.DocNumber'),
                ArrayHelper::getValue($raw_achievement, 'Document.IssueDate'),
                ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_achievement, 'Document.DocOrganization')),
                ArrayHelper::getValue($raw_achievement, $this->namesManagementService->getFullPackageAchievementCommentColumnName()),
                $this->application,
                ArrayHelper::getValue($raw_achievement, 'AchievementCategoryRef'),
                ArrayHelper::getValue($raw_achievement, 'Document.DocumentTypeRef'),
                ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_achievement, 'Document.DocumentCheckStatusRef', [])),
                (bool) ArrayHelper::getValue($raw_achievement, 'Document.ReadOnly', false)
            );

            $touched_ids[] = $ind_arch->id;

            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($ind_arch)
                ->update($raw_achievement['Files'] ?? []);
        }
        $ias_to_delete = (clone $ias_query)
            ->andFilterWhere(['not', [IndividualAchievement::tableName() . '.id' => $touched_ids]])
            ->all();

        
        foreach ($ias_to_delete as $ia) {
            $ia->archive(false);
        }
        return true;
    }
}
