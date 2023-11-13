<?php

namespace common\components;

use common\components\attachmentSaveHandler\exceptions\AttachmentViolationException;
use common\components\filesystem\FilterFilename;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\BaseAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\FileToSendInterface;
use common\models\SendingFile;
use common\models\UserRegulation;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class AttachmentManager
{

    private const INITIAL_CONFIG_TYPES = [
        'other' => ['doc', 'docx', 'docm'],
        'pdf' => ['pdf']
    ];

    private static $rememberedInitialConfigTypes = [];

    private static function getSystemTypesInitialSettings($typeId): array
    {
        $settings = [
            AttachmentType::SYSTEM_TYPE_TARGET => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Документы целевых договоров"; для менеджера скан-копий: `Документы целевых договоров`'
                ),
                'required' => true,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'from1c' => false,
                'campaign_code' => null,
                'document_type' => null,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_PREFERENCE => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Документы льгот и преимущественных прав"; для менеджера скан-копий: `Документы льгот и преимущественных прав`'
                ),
                'required' => true,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Документы индивидуальных достижений"; для менеджера скан-копий: `Документы индивидуальных достижений`'
                ),
                'required' => false,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_ABITURIENT_AVATAR => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Фото поступающего"; для менеджера скан-копий: `Фото поступающего`'
                ),
                'required' => true,
                'related_entity' => PageRelationManager::RELATED_ENTITY_QUESTIONARY,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Договоры об оказании платных образовательных услуг"; для менеджера скан-копий: `Договоры об оказании платных образовательных услуг`'
                ),
                'required' => false,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'document_type_guid' => Yii::$app->configurationManager->getCode('paid_contract_document_type'),
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_IDENTITY_DOCUMENT => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Документы подтверждающие личность"; для менеджера скан-копий: `Документы подтверждающие личность`'
                ),
                'required' => true,
                'related_entity' => PageRelationManager::RELATED_ENTITY_QUESTIONARY,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_EDUCATION_DOCUMENT => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Документы об образовании"; для менеджера скан-копий: `Документы об образовании`'
                ),
                'required' => true,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'is_using' => true,
                'hidden' => false,
            ],
            AttachmentType::SYSTEM_TYPE_APPLICATION_RETURN => [
                'name' => Yii::t(
                    'abiturient/attachment-widget/attachment-manager',
                    'Имя типа документа "Заявление на отзыв документов"; для менеджера скан-копий: `Заявление на отзыв документов`'
                ),
                'required' => false,
                'related_entity' => PageRelationManager::RELATED_ENTITY_APPLICATION,
                'is_using' => true,
                'hidden' => false,
            ],
        ];
        return $settings[$typeId];
    }

    public static function GetSystemAttachmentType($typeId)
    {
        if (!in_array($typeId, AttachmentType::GetSystemTypes(), true)) {
            throw new UserException('При поиске системного типа документа был передан неверный идентификатор системного типа документа.');
        }
        $type = AttachmentType::findOne([
            'system_type' => $typeId,
        ]);
        if (!$type) {
            $type = new AttachmentType(static::getSystemTypesInitialSettings($typeId));
            $type->system_type = $typeId;
            $type->save(false);
        }
        return $type;
    }

    




    public static function linkAttachment(AttachmentLinkableEntity $en, Attachment $attachment)
    {
        $junction_data = [
            $en::getEntityTableLinkAttribute() => $en->id,
            $en::getAttachmentTableLinkAttribute() => $attachment->id
        ];
        $already_linked = (new Query())->from($en::getTableLink())->where($junction_data)->exists();
        if (!$already_linked) {
            \Yii::$app->db->createCommand()->insert($en::getTableLink(), $junction_data)->execute();
        }
    }

    





    public static function unlinkAttachment(AttachmentLinkableEntity $en, Attachment $attachment): int
    {
        return \Yii::$app->db->createCommand()->delete($en->getTableLink(), [
            $en::getEntityTableLinkAttribute() => $en->id,
            $en::getAttachmentTableLinkAttribute() => $attachment->id
        ])->execute();
    }

    




    public static function unlinkAllAttachment(AttachmentLinkableEntity $en): int
    {
        return \Yii::$app->db->createCommand()
            ->delete($en->getTableLink(), [
                $en::getEntityTableLinkAttribute() => $en->id
            ])
            ->execute();
    }

    





    public static function unlinkAttachmentFromAll(Attachment $attachment): int
    {
        $errorFrom = '';
        $deleteSuccess = true;
        $id = $attachment->id;
        if (!EmptyCheck::isEmpty($id)) {
            foreach ($attachment->getAttachmentLinkDependency() as $class) {
                $link = new $class();
                $tableLink = $link->getTableLink();
                $exists = (new Query())->from($tableLink)->where(['attachment_id' => $id])->exists();
                if ($exists) {
                    $deleteSuccess = Yii::$app->db
                        ->createCommand()
                        ->delete($tableLink, ['attachment_id' => $id])
                        ->execute();
                    if (!$deleteSuccess) {
                        $errorFrom .= "{$tableLink} -> attachment_id -> {$id}\n";
                        break;
                    }
                }
            }
        }

        if (!$deleteSuccess) {
            Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
        }
        return $deleteSuccess;
    }

    




    public static function GetEntityWithEmptyFilesQuery(AttachmentLinkableEntity $en): ActiveQuery
    {
        $table = $en::getTableLink();
        $entityAttr = $en::getEntityTableLinkAttribute();
        $attachmentAttr = $en::getAttachmentTableLinkAttribute();
        $mainTableName = $en::getDbTableSchema()->name;
        $query = $en::getModel()::find()
            ->leftJoin("{$table} tt", "tt.{$entityAttr} = {$mainTableName}.id")
            ->where([
                "tt.{$attachmentAttr}" => null,
            ]);

        if ($en instanceof ArchiveModelInterface) {
            $query->andWhere([
                'not', [$en::getModel()::tableName() . '.' . $en::getArchiveColumn() => $en::getArchiveValue()]
            ]);
        }

        return $query;
    }

    








    public static function handleAttachmentUpload(array $attachments, array $regulations = []): array
    {
        $resultAttachments = [];

        foreach ($attachments as $attachment) {
            try {
                $newAttachments = $attachment->getAttachmentSaveHandler()->save();
            } catch (AttachmentViolationException $exception) {
                $attachment->addAttachmentsErrors([
                    $exception->getFileName() => $exception->getValidationErrors()
                ]);
            }
            if (!empty($newAttachments)) {
                $attachment->attachments = array_merge($attachment->attachments, $newAttachments);
                $resultAttachments = array_merge($resultAttachments, $newAttachments);
            }
        }

        foreach ($regulations as $regulation) {
            $regAttachment = $regulation->getAttachmentCollection();
            if ($regAttachment !== null) {
                $newAttachments = [];
                try {
                    $regAttachment->getAttachmentSaveHandler()->setHistoryInitiator($regulation->owner);
                    $newAttachments = $regAttachment->getAttachmentSaveHandler()->save();
                } catch (AttachmentViolationException $exception) {
                    $regAttachment->addAttachmentsErrors([
                        $exception->getFileName() => $exception->getValidationErrors()
                    ]);
                }
                foreach ($newAttachments as $regulation_attachment) {
                    $regulation->link('rawAttachments', $regulation_attachment);
                }
                $resultAttachments = array_merge($resultAttachments, $newAttachments);
            }
        }

        return $resultAttachments;
    }

    











    public static function buildAttachmentArrayTo1C(FileToSendInterface $table, DocumentType $file_document_type, $file_name = null)
    {
        
        if ((isset($table->deleted) && $table->deleted) || (isset($table->attachmentType) && $table->attachmentType->hidden)) {
            return null;
        }
        $abs_path = $table->getAbsPath();
        if ($abs_path && file_exists($abs_path) && !is_dir($abs_path)) {
            if (!$file_name) {
                $file_name = pathinfo($table->filename)['filename'];
            }
            $fileToSend = new SendingFile($table->linkedFile);
            return $fileToSend->buildArrayTo1C($file_document_type, $file_name);
        }
        return null;
    }

    public static function buildFileTo1C(File $file, DocumentType $document_type, ?string $filename, ?array $file_parts): array
    {
        if (!$filename) {
            $filename = pathinfo($file->upload_name)['filename'];
        }
        $filename = FilterFilename::sanitize($filename, false);

        $result = [
            'FileUID' => (string)$file->uid,
            'FileHash' => (string)$file->content_hash,
            'FileName' => (string)$filename,
            'FileExt' => (string)$file->extension,
            'FileTypeRef' => ReferenceTypeManager::GetReference($document_type),
        ];
        if ($file_parts) {
            $result['FileParts'] = $file_parts;
        }
        $result['Removed'] = 0;
        return $result;
    }

    








    public static function GetInitialPreviewConfigTypeByExtension($extension)
    {
        $extension = strtolower($extension);

        if (array_key_exists($extension, static::$rememberedInitialConfigTypes)) {
            return static::$rememberedInitialConfigTypes[$extension];
        }

        foreach (static::INITIAL_CONFIG_TYPES as $initialConfigType => $supportedExtensions) {
            if (in_array($extension, $supportedExtensions)) {
                static::$rememberedInitialConfigTypes[$extension] = $initialConfigType;
                return $initialConfigType;
            }
        }

        static::$rememberedInitialConfigTypes[$extension] = 'image';
        return 'image';
    }


    






    public static function GetMimeType($extension)
    {
        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
                break;
            default:
                return 'image/jpeg';
                break;
        }
    }

    






    public static function AttachFileToLinkableEntity(AttachmentLinkableEntity $entity, IReceivedFile $receivingFile): Attachment
    {
        $attachment_type = $entity->getAttachmentType();
        $application = ArrayHelper::getValue($entity, 'application');
        $questionary = ArrayHelper::getValue($entity, 'abiturientQuestionary');

        if (!$application && !$questionary) {
            $error_msg = "У сущности {$entity->getName()} отсутствует связь с анкетой или заявлением";
            Yii::error($error_msg);
            throw new UserException($error_msg);
        }
        $attachmentAttributes = [
            'attachment_type_id' => $attachment_type->id,
        ];
        if ($application) {
            $attachmentAttributes['owner_id'] = $application->user_id;
            $attachmentAttributes['application_id'] = $application->id;
        } else {
            $attachmentAttributes['owner_id'] = $questionary->user_id;
            $attachmentAttributes['questionary_id'] = $questionary->id;
        }

        $attachment = $entity->getAttachments()
            ->joinWith(['linkedFile'])
            ->andWhere([
                'attachment_type_id' => $attachment_type->id,
            ])
            ->andWhere([
                File::tableName() . '.content_hash' => $receivingFile->getHash(),
            ])
            ->one();
        if (!$attachment) {
            $attachment = new Attachment();
            $attachment->setAttributes($attachmentAttributes);
            $attachment->scenario = Attachment::SCENARIO_RECOVER;
            if (!$attachment->save()) {
                throw new RecordNotValid($attachment);
            }
        }
        $file = $receivingFile->getFile($attachment);
        $attachment->LinkFile($file);
        AttachmentManager::linkAttachment($entity, $attachment);
        return $attachment;
    }
}
