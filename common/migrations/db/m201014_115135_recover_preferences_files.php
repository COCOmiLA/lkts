<?php

use common\components\AttachmentManager;
use common\components\ini\iniSet;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use yii\base\UserException;
use yii\db\Query;




class m201014_115135_recover_preferences_files extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        iniSet::disableTimeLimit();

        try {
            $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_PREFERENCE);
        } catch (UserException $e) {
            Yii::error($e->getMessage());
            return false;
        }
        foreach ((new Query())->from(BachelorPreferences::tableName())->all() as $pref) {
            if (isset($pref['file'], $pref['filename']) && $pref['file'] !== "_") {
                $attachment = new Attachment();
                $attachment->setScenario(Attachment::SCENARIO_RECOVER);
                $attachment->attachment_type_id = $type->id;
                $attachment->file = $pref['file'];
                $attachment->filename = $pref['filename'];
                $attachment->deleted = false;
                $attachment->application_id = $pref['id_application'];
                $pref_application = BachelorApplication::findOne($pref['id_application']);
                if ($pref_application && \common\models\User::find()->where(['id' => $pref_application->user_id])->exists()) {
                    $attachment->owner_id = $pref_application->user_id;
                }
                if ($attachment->save()) {
                    $pref_instance = BachelorPreferences::findOne($pref['id']);
                    AttachmentManager::linkAttachment($pref_instance, $attachment);
                } else {
                    return false;
                }
            }
        }
    }

}
