<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;




class m210517_123559_resolve_old_manager_blocker_id extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $applications = BachelorApplication::find()->where([
            'not', ['blocker_id' => null]
        ])
        ->andWhere([
            'block_status' => BachelorApplication::BLOCK_STATUS_ENABLED
        ])->all();

        for($i = 0; $i < count($applications); $i++) {
            
            $application = $applications[$i];
            $oldManagerBlocker = $application->blocker;
            if(!is_null($oldManagerBlocker)) {
                $entrantManager = $oldManagerBlocker->getEntrantManagerEntity();
                $application->entrant_manager_blocker_id = $entrantManager->id;
                if($application->validate(['entrant_manager_blocker_id'])) {
                    $application->save(false, ['entrant_manager_blocker_id']);
                } else {
                    \Yii::error("Невозможно восстановить человека, который заблокировал заявление. \n" . print_r($application->errors, true), 'm210517_123559_resolve_old_manager_blocker_id');
                }
            }
        }
    }

    


    public function safeDown()
    {
        echo "m210517_123559_resolve_old_manager_blocker_id cannot be reverted.\n";

        return false;
    }

    













}
