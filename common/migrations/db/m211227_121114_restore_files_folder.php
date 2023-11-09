<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;
use common\models\AttachmentType;
use yii\helpers\FileHelper;




class m211227_121114_restore_files_folder extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $as = Attachment::find()
            ->joinWith(['attachmentType attachment_type_table'])
            ->andWhere(['attachment_type_table.system_type' => [AttachmentType::SYSTEM_TYPE_PREFERENCE, AttachmentType::SYSTEM_TYPE_TARGET]])
            ->all();
        $base_path = Yii::getAlias('@storage/web/preferences');

        foreach ($as as $attachment) {
            try {
                $old_folder_name = md5($attachment->application_id);
                $new_folder_name = md5($attachment->getOwnerId());


                $full_old_dir = FileHelper::normalizePath("$base_path/$old_folder_name");
                $full_new_dir = FileHelper::normalizePath("$base_path/$new_folder_name");
                if (file_exists($full_old_dir)) {
                    if (file_exists($full_new_dir)) {
                        $files = array_diff(scandir($full_old_dir), array('..', '.'));
                        foreach ($files as $file) {
                            copy(
                                FileHelper::normalizePath("$full_old_dir/$file"),
                                FileHelper::normalizePath("$full_new_dir/$file")
                            );
                        }
                    } else {
                        rename($full_old_dir, $full_new_dir);
                    }
                }
            } catch (Throwable $e) {
                echo $e->getMessage();
            }
        }
    }
}
