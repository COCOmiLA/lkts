<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\repository;


use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

class ChangeHistoryRepository
{
    private static $_checkpointDate = null;

    







    public static function getApplicationAndQuestionaryChangeHistoryByApplicationQuery(
        BachelorApplication $application,
        int $sortDirection = SORT_ASC,
        ?int $dateStart = null,
        ?int $dateEnd = null
    ): ActiveQuery {
        $app_changes = ChangeHistoryRepository::getChangeHistoryIds($application, true);

        $tnChangeHistory = ChangeHistory::tableName();
        $query = ChangeHistory::find()
            ->with([
                'initiator',
                'entrantManager',
                'changeHistoryEntityClasses.changeHistoryEntityClassInputs'
            ])
            ->where(["{$tnChangeHistory}.id" => $app_changes])
            ->orderBy([
                "{$tnChangeHistory}.created_at" => $sortDirection,
                "{$tnChangeHistory}.id" => $sortDirection,
            ]);

        if ($dateStart) {
            $query->andWhere(['>=', "{$tnChangeHistory}.created_at", $dateStart]);
        }
        if ($dateEnd && $dateEnd >= $dateStart) {
            $query->andWhere(['<=', "{$tnChangeHistory}.created_at", $dateEnd]);
        }

        return $query;
    }

    public static function getChangeHistoryIds(BachelorApplication $application, bool $add_questionary_changes): array
    {
        $checkpointDate = ChangeHistoryRepository::getCheckpointDate();
        $isAppCreatedAfterCheckpoint = $application->created_at > $checkpointDate;
        $current_app_changes = ChangeHistory::find()
            ->select(['change_history.id'])
            ->filterWhere([
                'change_history.questionary_id' => $add_questionary_changes ? ($application->abiturientQuestionary->id ?? null) : null
            ])
            ->orWhere([
                'change_history.application_id' => $application->id
            ])
            ->column();
        
        if ($isAppCreatedAfterCheckpoint && ($parent = $application->getParentDraft())) {
            $current_app_changes = [...$current_app_changes, ...ChangeHistoryRepository::getChangeHistoryIds($parent, false)];
        }
        return $current_app_changes;
    }

    




    private static function getCheckpointDate(): ?int
    {
        if (static::$_checkpointDate !== null) {
            return static::$_checkpointDate;
        }
        if (!Yii::$app->db->schema->getTableSchema('system_db_migration')) {
            return 2_147_483_646;
        }
        
        $latest_version_migration_timestamp = (new Query())
            ->select(['MAX(apply_time)'])
            ->from(['migrations' => 'system_db_migration'])
            ->where(['version' => 'm220809_125525_reduce_app_change_history_size'])
            ->scalar();
        if (!$latest_version_migration_timestamp) {
            static::$_checkpointDate = 2_147_483_646;
        } else {
            static::$_checkpointDate = (int)$latest_version_migration_timestamp;
        }
        return static::$_checkpointDate;
    }
}
