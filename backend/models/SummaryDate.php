<?php

namespace backend\models;

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\db\ActiveQuery;
use yii\db\Query;

class SummaryDate extends \yii\base\Model
{
    public $date;
    public $timestamp;
    public $new_users = 0;
    public $new_applications = 0;
    public $sended_applications = 0;
    public $approved_applications = 0;

    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
            'timestamp' => 'Дата',
            'new_users' => 'Количество новых регистраций',
            'new_applications' => 'Количество созданных заявлений',
            'sended_applications' => 'Количество поданных заявлений',
            'approved_applications' => 'Количество принятых заявлений',
        ];
    }

    public static function findAllAndCountTotal()
    {
        $listCountsData = [
            [
                'table' => BachelorApplication::class,
                'tableName' => BachelorApplication::tableName(),
                'dateTableField' => 'sent_at',
                'counterFieldName' => 'sended_applications',
                'statusFilters' => [
                    BachelorApplication::STATUS_SENT,
                    BachelorApplication::STATUS_REJECTED_BY1C,
                    BachelorApplication::STATUS_SENT_AFTER_APPROVED,
                    BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
                ],
                'draftStatusFilters' => [BachelorApplication::DRAFT_STATUS_SENT],
            ],
            [
                'table' => BachelorApplication::class,
                'tableName' => BachelorApplication::tableName(),
                'dateTableField' => 'approved_at',
                'counterFieldName' => 'approved_applications',
                'statusFilters' => [BachelorApplication::STATUS_APPROVED],
                'draftStatusFilters' => [BachelorApplication::DRAFT_STATUS_APPROVED],
            ]
        ];

        $summaries = [];
        $totalCounts = [];
        foreach ($listCountsData as $countData) {
            [
                'summaries' => $summaries,
                'totalCount' => $totalCount,
            ] = SummaryDate::getFilteredApplicationCounts(
                $countData['table'],
                $countData['tableName'],
                $countData['dateTableField'],
                $countData['counterFieldName'],
                $countData['statusFilters'],
                $countData['draftStatusFilters'],
                $summaries
            );

            $totalCounts[$countData['counterFieldName']] = $totalCount;
        }

        [
            'summaries' => $summaries,
            'totalCount' => $totalCount,
        ] = SummaryDate::getUserCounts(
            User::class,
            User::tableName(),
            'created_at',
            'new_users',
            $summaries
        );
        $totalCounts['new_users'] = $totalCount;

        [
            'summaries' => $summaries,
            'totalCount' => $totalCount,
        ] = SummaryDate::getOldestApplicationCounts(
            BachelorApplication::class,
            BachelorApplication::tableName(),
            'created_at',
            'new_applications',
            $summaries
        );
        $totalCounts['new_applications'] = $totalCount;

        return [
            'totalCount' => $totalCounts,
            'summaryDateAll' => $summaries,
        ];
    }

    








    private static function getUserCounts(
        string $table,
        string $tableName,
        string $dateTableField,
        string $counterFieldName,
        array  $summaries
    ) {
        $counter = SummaryDate::mainCounterQuery($table, $tableName, $dateTableField)
            ->andWhere([
                'IN',
                'id',
                RBACAuthAssignment::find()
                    ->select(['user_id'])
                    ->where(['IN', 'item_name', ['abiturient']])
            ]);

        return SummaryDate::getCounts($counter, $counterFieldName, $summaries);
    }

    










    private static function getFilteredApplicationCounts(
        string $table,
        string $tableName,
        string $dateTableField,
        string $counterFieldName,
        array  $statusFilters,
        array  $draftStatusFilters,
        array  $summaries
    ) {
        
        
        
        
        
        
        
        $selectors = ["DISTINCT {$tableName}.{$dateTableField} + {$tableName}.user_id AS id"];

        $counter = SummaryDate::mainCounterQuery($table, $tableName, $dateTableField, $selectors)
            ->andWhere(['NOT IN', "{$tableName}.draft_status", [$table::DRAFT_STATUS_MODERATING]]);

        if ($statusFilters) {
            $counter = $counter->andWhere(['IN', "{$tableName}.status", $statusFilters]);
        }
        if ($draftStatusFilters) {
            $counter = $counter->andWhere(['IN', "{$tableName}.draft_status", $draftStatusFilters]);
        }

        return SummaryDate::getCounts($counter, $counterFieldName, $summaries);
    }

    










    private static function getOldestApplicationCounts(
        string $table,
        string $tableName,
        string $dateTableField,
        string $counterFieldName,
        array  $summaries
    ) {
        $selectors = ["DISTINCT {$tableName}.type_id + {$tableName}.user_id AS id"];
        $counter = SummaryDate::mainCounterQuery($table, $tableName, $dateTableField, $selectors);

        return SummaryDate::getCounts($counter, $counterFieldName, $summaries);
    }

    






    private static function mainCounterQuery(string $table, string $tableName, string $dateTableField, $selectors = [])
    {
        if (!$selectors) {
            $selectors = ["{$tableName}.id"];
        }
        return $table::find()
            ->select(
                array_merge(
                    $selectors,
                    [IndependentQueryManager::toDate("{$tableName}.{$dateTableField}", 'date_created')]
                )
            )
            ->andWhere(['NOT IN', "{$tableName}.{$dateTableField}", [null, 0]]);
    }

    






    private static function getCounts(ActiveQuery $counter, string $counterFieldName, array $summaries)
    {
        $totalCount = 0;
        $newRecords = (new Query())
            ->select(['date_created', 'COUNT(id) as count'])
            ->from(['counter' => $counter])
            ->groupBy('date_created')
            ->all();

        foreach ($newRecords as $newRecord) {
            $date = $newRecord['date_created'];

            if (key_exists($date, $summaries)) {
                $summaries[$date]->{$counterFieldName} = $newRecord['count'];
            } else {
                $summary = new SummaryDate();
                $summary->date = $date;
                $summary->timestamp = strtotime($date);
                $summary->{$counterFieldName} = $newRecord['count'];

                $summaries[$date] = $summary;
            }

            $totalCount += $summaries[$date]->{$counterFieldName};
        }

        return [
            'summaries' => $summaries,
            'totalCount' => $totalCount,
        ];
    }
}
