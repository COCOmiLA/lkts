<?php

namespace common\components;

use common\components\exceptions\PhysicalPersonDuplicateException;




class ErrorMessageAnalyzer
{
    public static function getCustomException(\Throwable $e): \Throwable
    {
        if (static::isUserRefDuplicateError($e->getMessage())) {
            $userFriendlyMessage = \Yii::t('common', 'Сообщение пользователю при обнаружении дубля физического лица: `Физическое лицо с такими паспортными данными уже существует в системе. Обратитесь в приёмную кампанию.`');
            return new PhysicalPersonDuplicateException($userFriendlyMessage);
        }

        return $e;
    }

    



    public static function isUserRefDuplicateError(string $message): bool
    {
        $patterns = [
            'Для данного физического лица в текущей приемной кампании уже создано заявление',
            'Физическое лицо с такими паспортными данными уже существует в системе',
            'Найдены дубли физического лица',
            'У данного физического лица не указан СНИЛС',
            'У физического лица некорректно заполнен СНИЛС',
            \Yii::t('common', 'Сообщение пользователю при обнаружении дубля физического лица: `Физическое лицо с такими паспортными данными уже существует в системе. Обратитесь в приёмную кампанию.`')
        ];

        foreach ($patterns as $pattern) {
            if (mb_stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
