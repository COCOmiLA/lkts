<?php

namespace common\services\abiturientController\bachelor;

use common\components\AttachmentManager;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\services\abiturientController\bachelor\BachelorService;
use yii\web\UploadedFile;

class PaidContractService extends BachelorService
{
    




    public function uploadAttachment(
        User                $currentUser,
        BachelorApplication $application,
        BachelorSpeciality  $speciality
    ): void {
        $attachment = $this->getOrCreateAttachment($speciality);

        $attachment->file = UploadedFile::getInstanceByName("Attachment[file]");
        $attachment->owner_id = $currentUser->id;
        $attachment->application_id = $application->id;
        if ($attachment->upload()) {
            AttachmentManager::linkAttachment($speciality, $attachment);
        } else {
            $attachment->delete();
        }
    }

    




    public function getPathAndNameAttachment(BachelorSpeciality  $speciality): array
    {
        $emptyReturn = ['path' => '', 'fileName' => ''];

        if (empty($speciality)) {
            return $emptyReturn;
        }

        $attached = $speciality->getAttachedPaidContract();
        if (empty($attached)) {
            return $emptyReturn;
        }

        $abs_path = $attached->getAbsPath();
        if (
            $abs_path &&
            !is_null($attached->filename) &&
            !is_dir($abs_path) &&
            file_exists($abs_path)
        ) {
            $generated_file_name = $attached->filename;

            return ['path' => $abs_path, 'fileName' => $generated_file_name];
        }

        return $emptyReturn;
    }

    




    private function getOrCreateAttachment(BachelorSpeciality $speciality): Attachment
    {
        $attachment = $speciality->getAttachedPaidContract();
        if (empty($attachment)) {
            $attachment = new Attachment();
            $att_type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY);
            $attachment->attachment_type_id = $att_type->id;
        }

        return $attachment;
    }
}
