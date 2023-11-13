<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\interfaces\IDraftable;




class m220419_105557_remove_redundant_ques extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $latest_quest_ids = AbiturientQuestionary::find()
            ->active()
            ->andWhere(['status' => AbiturientQuestionary::STATUS_CREATE_FROM_1C])
            ->andWhere(['draft_status' => IDraftable::DRAFT_STATUS_APPROVED])
            ->select(['max(id)'])
            ->groupBy('user_id');
        $ques_to_delete_batches = AbiturientQuestionary::find()
            ->active()
            ->andWhere(['status' => AbiturientQuestionary::STATUS_CREATE_FROM_1C])
            ->andWhere(['draft_status' => IDraftable::DRAFT_STATUS_APPROVED])
            ->andWhere(['not', ['id' => $latest_quest_ids]])
            ->batch();
        foreach ($ques_to_delete_batches as $batch) {
            foreach ($batch as $q) {
                $q->delete();
            }
        }
    }

}
