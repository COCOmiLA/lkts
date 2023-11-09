<?php

namespace common\modules\abiturient\models\bachelor\extensions;

use common\components\PageRelationManager;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\errors\RecordNotValid;
use common\models\Regulation;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\IReceivedFile;

class AbiturientQuestionaryFileAttacher
{
    private $questionary;

    public function __construct(AbiturientQuestionary $questionary)
    {
        $this->questionary = $questionary;
    }

    






    public function attachFileToQuestionaryAttachments(IReceivedFile $receivingFile, array $attachmentTypeIds, File $file = null): ?File
    {
        $questionary_types = AttachmentType::GetCommonAttachmentTypesQuery([
            PageRelationManager::RELATED_ENTITY_REGISTRATION,
            PageRelationManager::RELATED_ENTITY_QUESTIONARY
        ]);
        $questionary_types = $questionary_types->andWhere(['at.id' => $attachmentTypeIds])->select(['at.id'])->column();
        if ($questionary_types) {
            foreach ($questionary_types as $questionary_type_id) {
                $attachment = Attachment::find()
                    ->joinWith(['linkedFile'])
                    ->andWhere([
                        'owner_id' => $this->questionary->user->id,
                        'attachment_type_id' => $questionary_type_id,
                        'questionary_id' => $this->questionary->id,
                    ])
                    ->andWhere([
                        File::tableName() . '.content_hash' => $receivingFile->getHash(),
                    ])
                    ->one();
                if (!$attachment) {
                    $attachment = new Attachment();
                    $attachment->owner_id = $this->questionary->user->id;
                    $attachment->questionary_id = $this->questionary->id;
                    $attachment->attachment_type_id = $questionary_type_id;
                    $attachment->scenario = Attachment::SCENARIO_RECOVER;

                    if (!$attachment->save()) {
                        throw new RecordNotValid($attachment);
                    }
                }

                if (!$file) {
                    $file = $receivingFile->getFile($attachment);
                }
                $attachment->LinkFile($file);
            }
        }
        return $file;
    }

    






    public function attachFileToQuestionaryRegulations(IReceivedFile $receivingFile, array $attachmentTypeIds, File $file = null): ?File
    {
        $userRegulations = $this->questionary
            ->getUserRegulations()
            ->joinWith([
                'regulation',
                'regulation.attachmentType',
            ])
            ->andWhere([
                AttachmentType::tableName() . '.id' => $attachmentTypeIds,
            ])
            ->andWhere([
                'or',
                [UserRegulation::tableName() . '.is_confirmed' => true],
                [Regulation::tableName() . '.confirm_required' => false],
            ])
            ->all();
        return $this->ensureFileAttachedToRegulations($userRegulations, $receivingFile, $file);
    }

    






    public function attachFileToUserRegulations(IReceivedFile $receivingFile, array $attachmentTypeIds, File $file = null): ?File
    {
        $userRegulations = $this->questionary->user->getCleanUserRegulations()
            ->joinWith([
                'regulation',
                'regulation.attachmentType',
            ])
            ->andWhere([
                AttachmentType::tableName() . '.id' => $attachmentTypeIds,
            ])
            ->andWhere([
                'or',
                [UserRegulation::tableName() . '.is_confirmed' => true],
                [Regulation::tableName() . '.confirm_required' => false],
            ])
            ->all();
        return $this->ensureFileAttachedToRegulations($userRegulations, $receivingFile, $file);
    }

    






    private function ensureFileAttachedToRegulations(array $userRegulations, IReceivedFile $receivingFile, ?File $file): ?File
    {
        foreach ($userRegulations as $userRegulation) {
            
            $attachment = $userRegulation
                ->getAttachments()
                ->joinWith(['linkedFile'])
                ->andWhere([
                    File::tableName() . '.content_hash' => $receivingFile->getHash(),
                ])
                ->one();
            if (!$attachment) {
                $attachment = new Attachment();
                $attachment->owner_id = $this->questionary->user->id;
                $attachment->attachment_type_id = $userRegulation->regulation->attachmentType->id;
                $attachment->scenario = Attachment::SCENARIO_RECOVER;
                if (!$attachment->save()) {
                    throw new RecordNotValid($attachment);
                }
                $userRegulation->link('rawAttachments', $attachment);
            }
            if (!$file) {
                $file = $receivingFile->getFile($attachment);
            }
            $attachment->LinkFile($file);
        }
        return $file;
    }
}