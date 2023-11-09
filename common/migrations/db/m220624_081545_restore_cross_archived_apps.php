<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;




class m220624_081545_restore_cross_archived_apps extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $bachelorApplicationTableName = BachelorApplication::tableName();
        $app_infos_to_check = BachelorApplication::find()
            ->select(["{$bachelorApplicationTableName}.user_id", "{$bachelorApplicationTableName}.type_id", "COUNT({$bachelorApplicationTableName}.id)"])
            ->groupBy(["{$bachelorApplicationTableName}.user_id", "{$bachelorApplicationTableName}.type_id"])
            ->andHaving(['>', "COUNT({$bachelorApplicationTableName}.id)", 1])
            ->asArray()
            ->all();
        foreach ($app_infos_to_check as $app_info) {
            $exists_active_app = BachelorApplication::find()
                ->where(['user_id' => $app_info['user_id'], 'type_id' => $app_info['type_id']])
                ->andWhere(['archive' => false])
                ->andWhere(['not', ['draft_status' => [IDraftable::DRAFT_STATUS_MODERATING, IDraftable::DRAFT_STATUS_CREATED]]])
                ->exists();
            if (!$exists_active_app) {
                $last_app = BachelorApplication::find()
                    ->where(['user_id' => $app_info['user_id'], 'type_id' => $app_info['type_id']])
                    ->andWhere(['archive' => true])
                    ->andWhere(['not', ['draft_status' => [IDraftable::DRAFT_STATUS_MODERATING, IDraftable::DRAFT_STATUS_CREATED]]])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->limit(1)
                    ->one();
                if ($last_app && $last_app->archive_reason != DraftsManager::REASON_RETURN) {
                    $last_app->archive = false;
                    $last_app->archived_at = null;
                    $last_app->save(false);
                }
            }
        }
    }

}
