<?php

use common\components\AttachmentManager;
use common\components\ini\iniSet;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use yii\db\Query;




class m201013_123354_recover_target_reception_documents extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        iniSet::disableTimeLimit();

        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_TARGET);
        foreach ((new Query())->from(BachelorTargetReception::tableName())->all() as $tar) {
            if (isset($tar['file'], $tar['filename']) && $tar['file'] !== "_") {
                $attachment = new Attachment();
                $attachment->setScenario(Attachment::SCENARIO_RECOVER);
                $attachment->attachment_type_id = $type->id;
                $attachment->file = $tar['file'];
                $attachment->filename = $tar['filename'];
                $attachment->deleted = false;
                $attachment->application_id = $tar['id_application'];
                $tar_application = BachelorApplication::findOne($tar['id_application']);
                if ($tar_application && \common\models\User::find()->where(['id' => $tar_application->user_id])->exists()) {
                    $attachment->owner_id = $tar_application->user_id;
                }
                if ($attachment->save()) {
                    $tar_instance = BachelorTargetReception::findOne($tar['id']);
                    AttachmentManager::linkAttachment($tar_instance, $attachment);
                } else {
                    return false;
                }
            }
        }
    }

}
