<?php

namespace backend\assets;

use common\assets\AdminLte;
use common\assets\BootstrapUtils;
use common\assets\Html5shiv;
use common\components\AssetManager\AssetBundleManager;
use yii\web\YiiAsset;

class BackendAsset extends AssetBundleManager
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = ['css/style.css'];

    public $js = ['admin_assets/main.js'];

    public $depends = [
        YiiAsset::class,
        AdminLte::class,
        Html5shiv::class,
        BootstrapUtils::class,
    ];
}
