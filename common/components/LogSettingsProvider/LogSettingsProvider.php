<?php

namespace common\components\LogSettingsProvider;

use common\models\UserProfile;
use Yii;

class LogSettingsProvider
{
    public static function infoLogPrefix()
    {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        if ($userID !== '-') {
            $userFullName = UserProfile::findOne(['user_id' => $userID])->getFullName();
        } else {
            $userFullName = '';
        }
        return sprintf('[%s][%s]', $userID, $userFullName);
    }

    public static function systemLogPrefixForDb()
    {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        $url = !Yii::$app->request->isConsoleRequest ? Yii::$app->request->getUrl() : null;
        return sprintf('[%s][%s][%s]', Yii::$app->id, $url, $userID);
    }

    public static function systemLogPrefixForFile()
    {
        $timeZone = ini_get('date.timezone');
        if (strlen((string)$timeZone) < 1) {
            $timeZone = 'UnSet';
        }
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        $url = !Yii::$app->request->isConsoleRequest ? Yii::$app->request->getUrl() : null;

        $version1C = Yii::$app->releaseVersionProvider->getVersion();

        $versionPortal = Yii::$app->version;
        $versionPHP = phpversion();
        return sprintf(
            'Portal_version=[%s] 1C_version=[%s] PHP_version=[%s] timeZome=[%s] [%s] [%s]',
            $versionPortal,
            $version1C,
            $versionPHP,
            $timeZone,
            $url,
            $userID
        );
    }
}
