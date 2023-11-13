<?php

namespace common\components\notification\factories;

use common\components\notification\ICanNotify;

interface INotifierFactory
{
    



    public function getNotifiers(array $types): array;
}
