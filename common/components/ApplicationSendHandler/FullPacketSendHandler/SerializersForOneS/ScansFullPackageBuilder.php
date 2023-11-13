<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;


use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\errors\RecordNotValid;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AlreadyReceivedFile;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\FilesManager;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\ReceivingFile;
use stdClass;
use yii\helpers\ArrayHelper;

class ScansFullPackageBuilder extends BaseApplicationPackageBuilder
{

    public function __construct(?BachelorApplication $app = null)
    {
        parent::__construct($app);
    }

    protected bool $allow_direct_fetching = false;

    public function setAllowDirectFetching(bool $allow_direct_fetching): ScansFullPackageBuilder
    {
        $this->allow_direct_fetching = $allow_direct_fetching;
        return $this;
    }

    


    protected $file_linkable_entity = null;

    public function build()
    {
        $result = [];
        if ($this->files_syncer) {
            $current_entity_files = $this->getCurrentEntityFiles();
            foreach ($current_entity_files as [$file_instance, $documentType, $filename]) {
                $result[] = $this->files_syncer->buildFileInfoTo1C($file_instance, $documentType, $filename);
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

        $processed_file_ids = [];
        foreach ($raw_data as $file_from_1c) {
            $file_from_1c_object = (object)$file_from_1c;
            
            $document_type = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $file_from_1c_object->FileTypeRef);

            $stored_file = $this->findLocalFileInStorage($file_from_1c_object, $document_type);
            if (!$stored_file) {
                $stored_file = $this->findReceivedFrom1CFile($file_from_1c_object, $document_type);
            }
            
            if (!$stored_file) {
                $stored_file = $this->buildFileFromParts($file_from_1c_object, $document_type);
            }
            if (!$stored_file && $this->allow_direct_fetching) {
                $stored_file = $this->fetchFileParts($file_from_1c_object, $document_type);
            }
            if ($this->files_syncer) {
                $this->files_syncer->appendAdditionalTempFiles(
                    ScansFullPackageBuilder::getPartsNames($file_from_1c_object->FileParts ?? [])
                );
            }
            if ($stored_file) {
                $stored_file->uid = $file_from_1c_object->FileUID;
                if (!$stored_file->save()) {
                    throw new RecordNotValid($stored_file);
                }
                $this->files_syncer->appendProcessedRawFile($file_from_1c);
                $processed_file_ids[] = $stored_file->id;
            }
        }
        $this->file_linkable_entity->removeNotPassedFiles($processed_file_ids);
        return true;
    }

    public function setFileLinkableEntity(ICanAttachFile $file_linkable_entity)
    {
        $this->file_linkable_entity = $file_linkable_entity;

        return $this;
    }

    protected function getCurrentEntityFiles()
    {
        $files_info = $this->file_linkable_entity->getAttachedFilesInfo();
        return array_values(array_filter($files_info, function (array $info) {
            return $info[0]->linkedFile && $info[0]->linkedFile->fileExists();
        }));
    }

    protected function findLocalFileInStorage(stdClass $file_from_1c_object, DocumentType $document_type): ?File
    {
        $stored_file = null;
        $exists_file = FilesManager::FindFileWithExistingContentWithoutFileNameCheck(
            $file_from_1c_object->FileExt,
            $file_from_1c_object->FileHash,
            null
        );
        if ($exists_file) {
            
            
            $received_file = new AlreadyReceivedFile(
                $exists_file,
                $document_type,
                "{$file_from_1c_object->FileName}.{$file_from_1c_object->FileExt}",
                $file_from_1c_object->FileHash,
                $file_from_1c_object->FileUID
            );
            $stored_file = $this->file_linkable_entity->attachFile($received_file, $document_type);
        }
        return $stored_file;
    }

    protected function findReceivedFrom1CFile(stdClass $file_from_1c_object, DocumentType $document_type): ?File
    {
        $stored_file = null;
        if ($this->files_syncer) {
            $received_file = $this->files_syncer->FindReceivedFile(
                $file_from_1c_object->FileName,
                $file_from_1c_object->FileExt,
                $file_from_1c_object->FileHash,
                $file_from_1c_object->FileUID
            );
            if ($received_file) {
                $stored_file = $this->file_linkable_entity->attachFile($received_file, $document_type);
            }
        }
        return $stored_file;
    }

    public static function getPartsNames($parts): array
    {
        if (!$parts) {
            return [];
        }
        if (!is_array($parts) || ArrayHelper::isAssociative($parts)) {
            $parts = [$parts];
        }
        usort($parts, function ($a, $b) {
            $a = ToAssocCaster::getAssoc($a);
            $b = ToAssocCaster::getAssoc($b);
            if ($a['PartNumber'] == $b['PartNumber']) {
                return 0;
            }
            return ($a['PartNumber'] < $b['PartNumber']) ? -1 : 1;
        });
        return array_map(
            function ($file) {
                $file = ToAssocCaster::getAssoc($file);
                return $file['PartFileName'];
            },
            $parts
        );
    }

    protected function buildFileFromParts(stdClass $file_from_1c_object, DocumentType $document_type): ?File
    {
        $stored_file = null;

        if (isset($file_from_1c_object->FileParts)) {
            $file_names = ScansFullPackageBuilder::getPartsNames($file_from_1c_object->FileParts);

            if ($file_names) {
                $received_file = (new ReceivingFile($file_from_1c_object))
                    ->setTempFileNames($file_names);
                $stored_file = $this->file_linkable_entity->attachFile($received_file, $document_type);
            }
        }
        return $stored_file;
    }

    protected function fetchFileParts(stdClass $file_from_1c_object, DocumentType $document_type): ?File
    {
        $receiving_file = new ReceivingFile($file_from_1c_object);
        $receiving_file->fetchFile();
        $this->files_syncer->appendReceivedFile($receiving_file);
        return $this->file_linkable_entity->attachFile($receiving_file, $document_type);
    }

}
