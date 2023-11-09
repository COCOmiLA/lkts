<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\interfaces;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;

interface ChangeDetailInterface
{
    public function setChange(ChangeHistory $change);

    public function getChange();
}