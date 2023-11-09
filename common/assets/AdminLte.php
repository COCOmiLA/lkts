<?php








namespace common\assets;

use yii\bootstrap4\BootstrapPluginAsset;
use yii\jui\JuiAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class AdminLte extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/dist';

    public $js = ['js/adminlte.min.js'];

    public $css = ['css/adminlte.min.css'];
    public $depends = [
        JqueryAsset::class,
        JuiAsset::class,
        BootstrapPluginAsset::class,
        FontAwesome::class,
        JquerySlimScroll::class
    ];
}
