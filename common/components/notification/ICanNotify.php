<?php

namespace common\components\notification;

use common\modules\abiturient\models\File;

interface ICanNotify
{
    






    public function send(string $title, string $body, array $user_ids, array $files = []): array;
}

