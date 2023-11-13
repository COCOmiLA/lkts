<?php

namespace common\models\interfaces;

use common\modules\abiturient\models\interfaces\IDraftable;
use yii\db\ActiveQuery;

interface ILinkedToParentDraft
{
    public function getParentDraft(): ?IDraftable;

    public function setParentDraft(?IDraftable $draftable): IArchiveWithInitiator;

    public function hasChildrenDrafts(): bool;

    public function getChildrenDrafts(): ActiveQuery;
}
