<?php

namespace common\services\abiturientController\sandbox;

use common\components\filesystem\FilterFilename;
use common\models\attachment\attachmentCollection\BaseAttachmentCollection;
use common\models\interfaces\FileToShowInterface;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\services\abiturientController\BaseService;
use Yii;
use yii\base\UserException;
use yii\bootstrap4\Html;
use yii\helpers\FileHelper;
use ZipArchive;

class AllApplicationAttachmentsService extends BaseService
{
    public const TYPE_APPLICATION = 'appli';
    public const TYPE_QUESTIONARY = 'quest';

    private const EMPTY_RETURN = [
        'hasError' => false,
        'filename' => '',
        'pathToZipArchive' => '',
    ];

    











    public function getZipArchiveAttribute(int $id, string $type)
    {
        [
            'application' => $application,
            'questionary' => $questionary,
        ] = $this->getApplicationAndQuestionary($id, $type);

        if (empty($questionary) && empty($application)) {
            return static::EMPTY_RETURN;
        }

        $zip = new ZipArchive();

        $personalData = $questionary->personalData;
        $filename = Html::encode("{$personalData->lastname}_{$personalData->firstname}_{$personalData->middlename}.zip");

        $tempZipDir = FileHelper::normalizePath(Yii::getAlias("@storage/web/tempZip/{$filename}"));
        if ($zip->open($tempZipDir, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            static::EMPTY_RETURN;
        }

        $collections = $this->getAttachmentCollections($application, $questionary);
        foreach ($collections as $collection) {
            $zip = $this->addCollectionToZip($zip, $collection);
        }

        if ($zip->numFiles < 1) {
            static::EMPTY_RETURN;
        }

        $pathToZipArchive = $zip->filename;
        return [
            'hasError' => false,
            'filename' => $filename,
            'pathToZipArchive' => $pathToZipArchive,
        ];
    }

    








    private function getApplicationAndQuestionary(int $id, string $type): array
    {
        $application = null;
        $questionary = null;

        if ($type == AllApplicationAttachmentsService::TYPE_APPLICATION) {
            $application = BachelorApplication::findOne($id);

            $questionary = $application->getAbiturientQuestionary()->one();
        } elseif ($type == AllApplicationAttachmentsService::TYPE_QUESTIONARY) {
            $questionary = AbiturientQuestionary::findOne($id);

            $application = $questionary->getLinkedBachelorApplication()->one();
        }

        return [
            'application' => $application,
            'questionary' => $questionary,
        ];
    }

    





    private function getAttachmentCollections(BachelorApplication $application, AbiturientQuestionary $questionary): array
    {
        $collections = [];
        if (!empty($questionary)) {
            $collections = array_merge($collections, $questionary->getEntireQuestionaryAttachmentCollections());
        }
        if (!empty($application)) {
            $collections = array_merge($collections, $application->getEntireApplicationAttachmentCollections());
        }

        return $collections;
    }

    







    private function addCollectionToZip(ZipArchive $zip, BaseAttachmentCollection $collection): ZipArchive
    {
        if (!$collection instanceof FileToShowInterface) {
            throw new UserException('Ожидалась сущность исполняющая интерфейс FileToShowInterface');
        }
        if ($collection->isHidden()) {
            return $zip;
        }

        $name = '';
        if ($collection->attachmentType && $collection->attachmentType->regulation) {
            $name = 'Нормативные документы/';
        }
        if (count($collection->attachments) > 1) {
            $name = FilterFilename::sanitize($collection->getAttachmentTypeName()) . '/';
        }
        $number = 1;
        foreach ($collection->attachments as $attachment) {
            $absPath = $attachment->getAbsPath();
            if (!$absPath || !file_exists($absPath)) {
                continue;
            }

            $sanitizedFilename = FilterFilename::sanitize("{$number}. {$attachment->getAttachmentTypeName()}.{$attachment->extension}");
            $zip->addFile($absPath, "{$name}{$sanitizedFilename}");
            $number++;
        }

        return $zip;
    }
}
