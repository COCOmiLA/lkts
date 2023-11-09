<?php

namespace common\modules\abiturient\assets\chatAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class AbiturientChatAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = [
        'chatAsset/generalized-chat.js',
    ];
    public $depends = [FrontendAsset::class];
}
