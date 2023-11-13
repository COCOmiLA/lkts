<?php


namespace common\components\IdentityManager;


use common\components\EntrantModeratorManager\interfaces\IEntrantManager;






class IdentityManager
{
    public static function GetIdentityForHistory(): ?IEntrantManager
    {
        $identity = null;
        if (isset(\Yii::$app->user)) {
            $identity = \Yii::$app->user->identity;
            if ($identity && $identity->isTransfer()) {
                $identity = $identity->getTransferUser();
            }
        }
        if (
            \Yii::$app->request->getIsConsoleRequest()
            || !isset(\Yii::$app->user)
            || \Yii::$app->user->isGuest
        ) {
            
            return null;
        } else {
            return $identity;
        }
    }
}
