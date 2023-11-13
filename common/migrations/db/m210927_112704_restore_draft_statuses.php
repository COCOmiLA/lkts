<?php

use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\db\Migration;




class m210927_112704_restore_draft_statuses extends Migration
{
    


    public function safeUp()
    {
        BachelorApplication::updateAll(
            ['draft_status' => IDraftable::DRAFT_STATUS_CREATED],
            ['status' => [
                ApplicationInterface::STATUS_CREATED
            ]]
        );

        BachelorApplication::updateAll(
            ['draft_status' => IDraftable::DRAFT_STATUS_SENT],
            ['status' => [
                ApplicationInterface::STATUS_SENT,
                ApplicationInterface::STATUS_SENT_AFTER_APPROVED,
                ApplicationInterface::STATUS_SENT_AFTER_NOT_APPROVED,
                ApplicationInterface::STATUS_NOT_APPROVED,
                ApplicationInterface::STATUS_REJECTED_BY1C,
            ]]
        );

        BachelorApplication::updateAll(
            ['draft_status' => IDraftable::DRAFT_STATUS_SENT],
            ['status' => [
                ApplicationInterface::STATUS_WANTS_TO_BE_REMOTE,
                ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL,
            ]]
        );

        BachelorApplication::updateAll(
            ['draft_status' => IDraftable::DRAFT_STATUS_APPROVED],
            ['status' => [
                ApplicationInterface::STATUS_APPROVED,
            ]]
        );

        AbiturientQuestionary::updateAll(['draft_status' => IDraftable::DRAFT_STATUS_CREATED]);
    }
}
