<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\db\Query;




class m220721_143515_restore_user_id_in_moderate_history extends MigrationWithDefaultOptions
{
    protected static $batch_size = 100;

    


    public function safeUp()
    {
        $declined_applications = BachelorApplication::find()->where([
            'status' => ApplicationInterface::STATUS_NOT_APPROVED,
            'draft_status' => IDraftable::DRAFT_STATUS_SENT
        ]);
        
        foreach ($declined_applications->each(static::$batch_size) as $application) {
            $history = (new Query())
                ->select(['id', 'user_id'])
                ->from('{{%application_moderate_history}}')
                ->where([
                    'application_id' => $application->id,
                    'status' => ApplicationInterface::STATUS_NOT_APPROVED,
                ])
                ->orderBy(['moderated_at' => SORT_DESC])
                ->limit(1)
                ->one();
            
            if ($history && $history['user_id'] === null && $application->last_manager_id) {
                $this->update('{{%application_moderate_history}}', 
                    ['user_id' => $application->last_manager_id], 
                    ['id' => $history['id']]
                );
            }
        }
    }

    


    public function safeDown()
    {
        return true;
    }
}
