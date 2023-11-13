<?php

namespace common\components\notification\widgets\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class PopupNotificationWidgetAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;
    
    public $css = ['css/notification_widget.css'];
    
    public $js = ['notification/notification_widget.js'];
    
    public $depends = [FrontendAsset::class];
}
