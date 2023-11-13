<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseApplicationPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ContractorPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ScansFullPackageBuilder;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\EmptyCheck;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class TargetPackageBuilder extends BaseApplicationPackageBuilder implements ILocalFinder
{
    public $targets;

    public function setTargets(array $targs)
    {
        $this->targets = $targs;
        return $this;
    }

    public function build()
    {
        $return = [];
        foreach ($this->targets as $target) {
            

            $docRef = $target->documentType;
            if (empty($docRef) && !EmptyCheck::isEmpty($target->document_type)) {
                
                $docRef = DocumentType::findByCode($target->document_type);
                $target->document_type_id = ArrayHelper::getValue($docRef, 'id');
                $target->save(true, ['document_type_id']);
            }
            $is_removed = $target->isArchive();
            $tmp = [
                'TargetTempGUID' => $target->tmp_uuid,
                'Document' => [
                    'DocumentTypeRef' => ReferenceTypeManager::GetReference($docRef),
                    'DocSeries' => $target->document_series,
                    'DocNumber' => $target->document_number,
                    'DocOrganization' => (new ContractorPackageBuilder(null, $target->documentContractor))->build(),
                    'IssueDate' => date('Y-m-d', strtotime($target->document_date) ?: strtotime(TargetPackageBuilder::EMPTY_DATE)),
                    'DocumentCheckStatusRef' => $target->buildDocumentCheckStatusRefType(),
                    'ReadOnly' => $target->read_only ? 1 : 0,
                ],
                'TargetOrganization' => (new ContractorPackageBuilder(null, $target->targetContractor))->build(),
                'Removed' => $is_removed ? 1 : 0,
            ];
            if (!$is_removed) {
                $files = (new ScansFullPackageBuilder($this->application))
                    ->setFilesSyncer($this->files_syncer)
                    ->setFileLinkableEntity($target)
                    ->build();
                if ($files) {
                    $tmp['Files'] = $files;
                }
            }
            $return[] = $tmp;
        }
        return $return;
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
        foreach ($raw_data as $raw_target) {
            
            $local_target = $this->findLocalByRaw($raw_target, $touched_ids);
            $touched_ids[] = $local_target->id;
            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($local_target)
                ->update($raw_target['Files'] ?? []);
        }
        foreach ($this->application->getBachelorTargetReceptions()->andFilterWhere(['not in', 'id', $touched_ids])->all() as $item_to_delete) {
            $item_to_delete->delete();
        }
        return true;
    }

    public function findLocalByRaw($raw_record, $excluded_ids = []): ?ActiveRecord
    {
        return BachelorTargetReception::GetOrCreateFromRaw(
            ArrayHelper::getValue($raw_record, 'TargetOrganization'),
            ArrayHelper::getValue($raw_record, 'Document.DocSeries'),
            ArrayHelper::getValue($raw_record, 'Document.DocNumber'),
            ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_record, 'Document.DocOrganization')),
            ArrayHelper::getValue($raw_record, 'Document.IssueDate'),
            ArrayHelper::getValue($raw_record, 'Document.DocumentTypeRef'),
            ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_record, 'Document.DocumentCheckStatusRef', [])),
            (bool) ArrayHelper::getValue($raw_record, 'Document.ReadOnly', false),
            $this->application,
            $excluded_ids
        );
    }
}
