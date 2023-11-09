<?php

namespace common\modules\abiturient\models\chat;

use yii\helpers\Url;

class ManagerChatFile extends ChatFileBase
{
    public function getFileDownloadUrl(): ?string
    {
        return Url::to(['manager-chat/download', 'id' => $this->id]);
    }
}
