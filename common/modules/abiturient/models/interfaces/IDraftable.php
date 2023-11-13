<?php

namespace common\modules\abiturient\models\interfaces;

interface IDraftable
{
    const DRAFT_STATUS_CREATED = 1;
    const DRAFT_STATUS_SENT = 2;
    const DRAFT_STATUS_MODERATING = 3;
    const DRAFT_STATUS_APPROVED = 4;

    public function translateDraftStatus(): string;
}