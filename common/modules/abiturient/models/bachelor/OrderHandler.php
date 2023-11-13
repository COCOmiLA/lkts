<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\BooleanCaster;
use common\models\ToAssocCaster;
use yii\helpers\ArrayHelper;

class OrderHandler
{
    public static function ProcessOrder(BachelorApplication $application, $raw_order): bool
    {
        if (!$raw_order) {
            return false;
        }
        $raw_order = ToAssocCaster::getAssoc($raw_order);

        if ($raw_order && isset($raw_order['Receipt'])) {
            $application->have_order = BooleanCaster::cast($raw_order['Receipt']);
            $application->order_info = null;
            if ($application->have_order) {
                $application->order_info = (string)$raw_order['OrderInfo'];
            }
            return $application->save(true, ['have_order', 'order_info']);
        }
        return false;
    }
}