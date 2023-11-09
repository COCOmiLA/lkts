<?php

namespace common\services\abiturientController\questionary;

use common\components\UUIDManager;
use common\models\AbiturientAvatar;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\AlreadyReceivedFile;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\FilesManager;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Yii;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\UploadedFile;



class AvatarService extends AbiturientQuestionaryService
{
    




    public function uploadAvatar(AbiturientQuestionary $questionary): array
    {
        $avatarFromPost = UploadedFile::getInstanceByName('file');
        if ($avatarFromPost !== null) {
            $abitAvatar = $this->getAvatar($questionary);

            $abitAvatar->file = $avatarFromPost;
            if ($abitAvatar->validate()) {
                return $this->saveAvatarProcess($abitAvatar, $avatarFromPost);
            }

            return [
                'error' => print_r($abitAvatar->errors, true),
                'status' => false,
                'fileLink' => '',
            ];
        }

        return [
            'error' => 'Не удалось загрузить файл',
            'status' => false,
            'fileLink' => '',
        ];
    }

    




    public function deleteAvatar(AbiturientQuestionary $questionary): bool
    {
        $abiturientAvatar = $questionary->abiturientAvatar;
        if (!$abiturientAvatar) {
            return false;
        }

        return $abiturientAvatar->archive();
    }

    




    private function getAvatar(AbiturientQuestionary $questionary): AbiturientAvatar
    {
        $abitAvatar = $questionary->getComputedAbiturientAvatar();
        if (!$abitAvatar->isNewRecord) {
            $attachmentType = $abitAvatar->attachmentType;
            $abitAvatar->safeDelete(Yii::$app->user->identity);

            $abitAvatar = new AbiturientAvatar();
            $abitAvatar->attachment_type_id = $attachmentType->id;
            $abitAvatar->deleted = false;
        }
        $abitAvatar->owner_id = $questionary->user_id;
        $abitAvatar->questionary_id = $questionary->id;

        return $abitAvatar;
    }

    





    private function saveAvatarProcess(
        AbiturientAvatar $abitAvatar,
        UploadedFile     $avatarFromPost
    ): array
    {
        $tmpFile = $this->buildTmpFile($avatarFromPost);
        $abitAvatar->file = $this->getOrCreateByTempAvatar($abitAvatar, $avatarFromPost, $tmpFile);

        if ($abitAvatar->upload()) {
            $this->changeHistoryAndCleanUp($abitAvatar, $tmpFile);

            return [
                'error' => '',
                'status' => true,
                'fileLink' => $abitAvatar->getFileDownloadUrl()
            ];
        }
        if (file_exists($tmpFile) && !is_dir($tmpFile)) {
            FileHelper::unlink($tmpFile);
        }
        return [
            'error' => 'Не удалось сохранить файл',
            'status' => false,
            'fileLink' => '',
        ];
    }

    




    private function resizeAvatar(UploadedFile $avatarFromPost): ImageInterface
    {
        $imagine = Image::getImagine();
        $resizedImage = $imagine->open(Yii::getAlias($avatarFromPost->tempName));

        $selected_height = abs(intval($this->request->post('h')));
        $selected_width = abs(intval($this->request->post('w')));

        if ($selected_height != 0 && $selected_width != 0) {
            $x = abs(intval($this->request->post('x')));
            $y = abs(intval($this->request->post('y')));
            $showed_image_width = abs(intval($this->request->post('width')));
            $showed_image_height = abs(intval($this->request->post('height')));


            $real_image_width = $resizedImage->getSize()->getWidth();
            $real_image_height = $resizedImage->getSize()->getHeight();

            $map_multiplier_width = $real_image_width / $showed_image_width;
            $map_multiplier_height = $real_image_height / $showed_image_height;
            $mapped_selected_height = $selected_height * $map_multiplier_height;
            $mapped_selected_width = $selected_width * $map_multiplier_width;
            $mapped_x = $x * $map_multiplier_width;
            $mapped_y = $y * $map_multiplier_height;

            $resizedImage = Image::crop($resizedImage, $mapped_selected_width, $mapped_selected_height, [$mapped_x, $mapped_y]);
        }
        return $resizedImage->resize(new Box(200, 200));
    }

    




    private function buildTmpFile(UploadedFile $avatarFromPost): string
    {
        $originalHash = FilesManager::GetFileHash($avatarFromPost->tempName);
        $extension = $avatarFromPost->extension;

        return FileHelper::normalizePath(sys_get_temp_dir() .
            '/' .
            mb_strtolower($originalHash) .
            str_replace('-', '', UUIDManager::GetUUID()) .
            '.' .
            $extension);
    }

    






    private function getOrCreateByTempAvatar(
        AbiturientAvatar $abitAvatar,
        UploadedFile     $avatarFromPost,
        string           $tmpFile
    ): AlreadyReceivedFile
    {
        $saveOptions = ['jpeg_quality' => 100, 'png_compression_level' => 1];
        $image = $this->resizeAvatar($avatarFromPost);
        $image->save($tmpFile, $saveOptions);

        $uploadFilename = $avatarFromPost->name;
        $fileHash = FilesManager::GetFileHash($tmpFile);
        $file = File::GetOrCreateByTempFile(
            $abitAvatar->getPathToStoreFiles(),
            [
                $uploadFilename,
                pathinfo($uploadFilename, PATHINFO_EXTENSION),
                $fileHash,
                null,
                function (string $path) use ($tmpFile): bool {
                    return copy($tmpFile, $path);
                }
            ]
        );

        return new AlreadyReceivedFile($file, null, $uploadFilename, $fileHash, null);
    }

    





    private function changeHistoryAndCleanUp(AbiturientAvatar $abitAvatar, string $tmpFile): void
    {
        $abitAvatar->getChangeHistoryHandler()->getInsertHistoryAction()->proceed();
        
        if (file_exists($tmpFile) && !is_dir($tmpFile)) {
            FileHelper::unlink($tmpFile);
        }
    }
}
