<?php

namespace common\modules\abiturient\assets\notificationAsset;

use common\assets\ChangeGridViewPaginationAsset;
use frontend\assets\FrontendAsset;
use common\components\AssetManager\AssetBundleManager;

class NotificationAsset extends AssetBundleManager
{
    public $sourcePath = '@common/modules/abiturient/assets/notificationAsset';

    public $css = ['css/notification.css'];

    public $js = ['js/notification.js'];

    public $depends = [
        FrontendAsset::class,
        ChangeGridViewPaginationAsset::class,
    ];
}
