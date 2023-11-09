<?php

namespace common\modules\student\components\chart\assets;

use common\components\AssetManager\AssetBundleManager;

class ChartAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['chart/chart.js'];
    public $css = ['css/chart.css'];

    public $depends = [
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'yii\web\YiiAsset',
        'frontend\assets\FrontendAsset',
        'common\assets\DatePicker',
    ];
}
