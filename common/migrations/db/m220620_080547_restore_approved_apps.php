<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;




class m220620_080547_restore_approved_apps extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tn = BachelorApplication::tableName();
        $duplicated = BachelorApplication::find()
            ->select(["{$tn}.user_id", "{$tn}.type_id", "count({$tn}.id)",])
            ->groupBy(["{$tn}.user_id", "{$tn}.type_id"])
            ->active()
            ->andWhere([
                "{$tn}.draft_status" => \common\modules\abiturient\models\interfaces\IDraftable::DRAFT_STATUS_APPROVED,
            ])
            ->andHaving(['>', "count({$tn}.id)", 1])
            ->asArray()
            ->all();

        foreach ($duplicated as $info) {
            $user_id = $info['user_id'];
            $type_id = $info['type_id'];
            if (!$user_id || !$type_id) {
                continue;
            }
            $latest_id = BachelorApplication::find()
                ->select(["{$tn}.id"])
                ->limit(1)
                ->active()
                ->andWhere([
                    "{$tn}.user_id" => $user_id,
                    "{$tn}.type_id" => $type_id,
                ])
                ->andWhere([
                    "{$tn}.draft_status" => \common\modules\abiturient\models\interfaces\IDraftable::DRAFT_STATUS_APPROVED,
                ])
                ->orderBy(["{$tn}.updated_at" => SORT_DESC])
                ->scalar();
            $to_delete = BachelorApplication::find()
                ->active()
                ->andWhere([
                    "{$tn}.user_id" => $user_id,
                    "{$tn}.type_id" => $type_id,
                ])
                ->andWhere([
                    "{$tn}.draft_status" => \common\modules\abiturient\models\interfaces\IDraftable::DRAFT_STATUS_APPROVED,
                ])
                ->andWhere(['not', ["{$tn}.id" => $latest_id]])
                ->all();
            try {
                foreach ($to_delete as $app) {
                    $app->delete();
                }
            } catch (Throwable $e) {
                Yii::error("Ошибка при восстановлении принятых заявлений: {$e->getMessage()}");
            }
        }
    }

}
