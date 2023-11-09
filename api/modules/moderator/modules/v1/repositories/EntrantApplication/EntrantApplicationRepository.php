<?php

namespace api\modules\moderator\modules\v1\repositories\EntrantApplication;


use api\modules\moderator\modules\v1\models\EntrantApplication\decorators\EntrantApplicationModifiedViewDecorated;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\db\ActiveQuery;

class EntrantApplicationRepository
{
    public static function GetReadyEntrantApplicationListByCampaignReferenceTypeQuery(StoredReferenceType $ref): ActiveQuery
    {
        return EntrantApplicationModifiedViewDecorated::find()
            ->innerJoinWith('user')
            ->joinWith('type.campaign.referenceType ref')
            ->andWhere([
                'ref.reference_uid' => $ref->reference_uid
            ])
            ->andWhere([
                'bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT,
                'bachelor_application.archive' => false,
            ])->andWhere([
                'bachelor_application.status' => [
                    BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
                    BachelorApplication::STATUS_SENT,
                    BachelorApplication::STATUS_SENT_AFTER_APPROVED,
                    BachelorApplication::STATUS_REJECTED_BY1C,
                    BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
                ]
            ]);
    }

}