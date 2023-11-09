<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseApplicationPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ContractorPackageBuilder;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ScansFullPackageBuilder;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\EmptyCheck;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class OlympicPackageBuilder extends BaseApplicationPackageBuilder implements ILocalFinder
{
    public $prefs;

    public function setPrefenences(array $prefs)
    {
        $this->prefs = $prefs;
        return $this;
    }

    public function build()
    {
        $return = [];
        foreach ($this->prefs as $pref) {
            

            $docRef = $pref->documentType;
            if (empty($docRef) && !EmptyCheck::isEmpty($pref->document_type)) {
                
                $docRef = DocumentType::findByCode($pref->document_type);
                $pref->document_type_id = ArrayHelper::getValue($docRef, 'id');
                $pref->save(true, ['document_type_id']);
            }
            $is_removed = $pref->isArchive();
            $tmp = [
                'OlympicTempGUID' => $pref->tmp_uuid,
                'Document' => [
                    'DocumentTypeRef' => ReferenceTypeManager::GetReference($docRef),
                    'DocSeries' => $pref->document_series,
                    'DocNumber' => $pref->document_number,
                    'DocOrganization' => (new ContractorPackageBuilder(null, $pref->contractor))->build(),
                    'IssueDate' => date('Y-m-d', strtotime($pref->document_date) ?: strtotime(OlympicPackageBuilder::EMPTY_DATE)),
                    'DocumentCheckStatusRef' => $pref->buildDocumentCheckStatusRefType(),
                    'ReadOnly' => $pref->read_only ? 1 : 0,
                ],
                'SpecialMarkRef' => ReferenceTypeManager::GetReference($pref, 'specialMark'),
                'OlympicRef' => ReferenceTypeManager::GetReference($pref, 'olympiad'),
                'Removed' => $is_removed ? 1 : 0,
            ];
            if (!$is_removed) {
                $files = (new ScansFullPackageBuilder($this->application))
                    ->setFilesSyncer($this->files_syncer)
                    ->setFileLinkableEntity($pref)
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

        foreach ($raw_data as $raw_benefit) {
            
            $local_benefit = $this->findLocalByRaw($raw_benefit, $touched_ids);
            $touched_ids[] = $local_benefit->id;

            (new ScansFullPackageBuilder($this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setFileLinkableEntity($local_benefit)
                ->update($raw_benefit['Files'] ?? []);
        }

        foreach ($this->application->getBachelorPreferencesOlymp()->andFilterWhere(['not in', 'id', $touched_ids])->all() as $item_to_delete) {
            $item_to_delete->delete();
        }
        return true;
    }

    public function findLocalByRaw($raw_record, $excluded_ids = []): ?ActiveRecord
    {
        return BachelorPreferences::GetOrCreateFromRaw(
            ArrayHelper::getValue($raw_record, 'Document.DocSeries'),
            ArrayHelper::getValue($raw_record, 'Document.DocNumber'),
            ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_record, 'Document.DocOrganization')),
            ArrayHelper::getValue($raw_record, 'Document.IssueDate'),
            null,
            ArrayHelper::getValue($raw_record, 'OlympicRef'),
            ArrayHelper::getValue($raw_record, 'SpecialMarkRef'),
            ArrayHelper::getValue($raw_record, 'Document.DocumentTypeRef'),
            ToAssocCaster::getAssoc(ArrayHelper::getValue($raw_record, 'Document.DocumentCheckStatusRef', [])),
            (bool) ArrayHelper::getValue($raw_record, 'Document.ReadOnly', false),
            $this->application,
            $excluded_ids
        );
    }
}
