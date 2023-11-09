<?php

namespace common\modules\student\components\schedule\assets;

use common\components\AssetManager\AssetBundleManager;

class ScheduleAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/schedule.css'];

    public $depends = [
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'yii\web\YiiAsset',
        'frontend\assets\FrontendAsset',
    ];
}
