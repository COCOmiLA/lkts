<?php

namespace common\components\notification\repositories;

use common\models\notification\Notification;
use yii\db\ActiveQuery;
use yii\db\Query;

class PopupNotificationRepository
{
    



    public static function getReceiverNotifications($user_id): ActiveQuery
    {
        return Notification::find()->andWhere(['receiver_id' => $user_id])
            ->with('chatManager')
            ->orderBy(['created_at' => SORT_DESC]);
    }
    
    public static function getMaxId(int $user_id): int
    {
        $max = static::getReceiverNotifications($user_id)->select('id')->limit(1)->scalar();
        return intval($max);
    }

    public static function getNew(int $user_id, int $max_id): ActiveQuery
    {
        return PopupNotificationRepository::getReceiverNotifications($user_id)
            ->andWhere(['>', 'id', $max_id]);            
    }
    
    



    public static function getUnreadCount($user_id): ActiveQuery
    {
        return static::getReceiverNotifications($user_id)->andWhere(['is', 'read_at', null]);
    }
    
    



    public static function getListOfAdmissionCampaignQuery($userId): Query
    {
        
        return (new Query())
            ->select(['admission_campaign_reference_type.reference_uid', 'application_type.name'])
            ->from('application_type')
            ->leftJoin('admission_campaign', 'admission_campaign.id = application_type.campaign_id')
            ->leftJoin('admission_campaign_reference_type', 'admission_campaign_reference_type.id = admission_campaign.ref_id');
    }

    



    public static function getListOfAdmissionCampaignNonArchive($userId): array
    {
        return static::getListOfAdmissionCampaignQuery($userId)
            ->andWhere([
                'admission_campaign.archive' => false
            ])
            ->all();
    }
}
