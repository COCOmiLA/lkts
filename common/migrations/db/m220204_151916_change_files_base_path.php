<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\File;
use yii\helpers\FileHelper;




class m220204_151916_change_files_base_path extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $files = File::find()->all();
        foreach ($files as $file) {
            $file->calcFileHash();
            $file->save(false);
        }

        
        $file_ids = File::find()->select('id')->column();

        foreach ($file_ids as $file_id) {
            $file = File::findOne($file_id);
            if (!$file) {
                continue;
            }
            $exploded = explode('/', $file->base_path);
            $user_dir = $exploded[count($exploded) - 1];
            $base_dir = str_replace($user_dir, '', $file->base_path);
            $file_in_right_place = $base_dir === '@storage/web/scans/';
            $correct_files = $this->getCorrectPlacedFiles($file, $user_dir);
            $correct_file = null;
            if ($correct_files) {
                if (!$file_in_right_place) {
                    $correct_file = $correct_files[0];
                    if (count($correct_files) > 1) {
                        
                        foreach (array_slice($correct_files, 1) as $file_to_replace) {
                            $this->reassignLinks($file_to_replace, $correct_file);
                        }
                    }
                } else {
                    foreach ($correct_files as $file_to_replace) {
                        $this->reassignLinks($file_to_replace, $file);
                    }
                }
            }
            if ($file_in_right_place) {
                continue;
            }
            if ($correct_file) {
                $this->reassignLinks($file, $correct_file);
            } else {
                
                $correct_dir_path = "@storage/web/scans/{$user_dir}";
                $old_path = $file->getFilePath();
                if (file_exists($old_path)) {
                    $new_dir_path = FileHelper::normalizePath(Yii::getAlias($correct_dir_path));
                    if (!file_exists($new_dir_path)) {
                        FileHelper::createDirectory($new_dir_path);
                    }
                    copy(
                        $old_path,
                        FileHelper::normalizePath($new_dir_path . DIRECTORY_SEPARATOR . $file->real_file_name)
                    );
                }
                $file->base_path = $correct_dir_path;
                $file->save(false);
            }

        }
    }

    protected function getCorrectPlacedFiles(File $file, string $user_dir): array
    {
        return File::find()
            ->where(['not', ['id' => $file->id]])
            ->andWhere([
                'base_path' => "@storage/web/scans/{$user_dir}",
                'upload_name' => $file->upload_name,
                'content_hash' => $file->content_hash,
            ])
            ->all();
    }

    protected function reassignLinks(File $from, File $to): void
    {
        if ($from->id == $to->id) {
            return;
        }

        $linkedList = $from->getLinkedAttachments()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $linkedList = $from->getLinkedConsents()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $linkedList = $from->getLinkedDocumentTemplates()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $linkedList = $from->getLinkedArchivedAttachments()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $linkedList = $from->getLinkedAdmissionAgreements()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $linkedList = $from->getLinkedAgreementDeclines()->all();
        foreach ($linkedList as $link) {
            $this->reassignLink($from, $to, $link);
        }
        $from->delete();
    }

    protected function reassignLink(File $from, File $to, $entity): void
    {
        $entity->unlink('linkedFile', $from, true);
        $entity->link('linkedFile', $to);
    }
}
