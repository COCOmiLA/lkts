<?php

namespace common\components\notification;

use common\models\notification\Notification;
use yii\base\BaseObject;

abstract class BaseNotifier extends BaseObject implements ICanNotify
{
    public $batch_size = 1000;
    public $sender_id;
    public $category = Notification::CATEGORY_COMMON;
}
