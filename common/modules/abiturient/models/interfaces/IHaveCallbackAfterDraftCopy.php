<?php

namespace common\modules\abiturient\models\interfaces;

use common\models\interfaces\FileToSendInterface;

interface IHaveCallbackAfterDraftCopy
{
    public function afterDraftCopy(FileToSendInterface $from): void;
}