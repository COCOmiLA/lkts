<?php

namespace common\services\abiturientController\bachelor\entrant_test;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\services\abiturientController\bachelor\BachelorService;

class CentralizedTestingService extends BachelorService
{
    




    public function getCentralizedTesting(BachelorApplication $application, int $egeId): ?BachelorResultCentralizedTesting
    {
        $tnEgeResult = EgeResult::tableName();
        $tnBachelorResultCentralizedTesting = BachelorResultCentralizedTesting::tableName();

        return BachelorResultCentralizedTesting::find()
            ->joinWith('egeResult')
            ->andWhere([
                "{$tnEgeResult}.application_id" => $application->id,
                "{$tnBachelorResultCentralizedTesting}.id" => $egeId,
            ])
            ->active()
            ->one();
    }
}
