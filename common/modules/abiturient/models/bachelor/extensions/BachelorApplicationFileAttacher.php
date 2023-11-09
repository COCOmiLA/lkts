<?php

namespace common\modules\abiturient\models\bachelor\extensions;

use common\components\PageRelationManager;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\errors\RecordNotValid;
use common\models\Regulation;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use yii\helpers\ArrayHelper;

class BachelorApplicationFileAttacher
{
    private BachelorApplication $application;

    public function __construct(BachelorApplication $application)
    {
        $this->application = $application;
    }

    






    public function attachFileToApplicationAttachments(IReceivedFile $receivingFile, array $attachmentTypeIds, File $file = null): ?File
    {
        $application_types = AttachmentType::GetUnionAttachmentTypes(
            $this->application->type->rawCampaign->referenceType->reference_uid,
            PageRelationManager::GetFullRelatedListForApplication()
        );
        $application_types = array_values(array_filter($application_types->all(), function (AttachmentType $at) use ($attachmentTypeIds) {
            return in_array($at->id, $attachmentTypeIds);
        }));
        $application_types = ArrayHelper::getColumn($application_types, 'id');

        if ($application_types) {
            foreach ($application_types as $application_type_id) {
                $attachment = Attachment::find()
                    ->joinWith(['linkedFile'])
                    ->andWhere([
                        'owner_id' => $this->application->user->id,
                        'attachment_type_id' => $application_type_id,
                        'application_id' => $this->application->id,
                    ])
                    ->andWhere([
                        File::tableName() . '.content_hash' => $receivingFile->getHash(),
                    ])
                    ->one();
                if (!$attachment) {
                    $attachment = new Attachment();
                    $attachment->owner_id = $this->application->user->id;
                    $attachment->application_id = $this->application->id;
                    $attachment->attachment_type_id = $application_type_id;
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

    






    public function attachFileToApplicationRegulations(IReceivedFile $receivingFile, array $attachmentTypeIds, File $file = null): ?File
    {
        $userRegulations = $this->application->getRegulations()
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
                $attachment->owner_id = $this->application->user->id;
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