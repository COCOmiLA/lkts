<?php







namespace frontend\assets;

use common\assets\BootstrapUtils;
use common\assets\ClientErrorReceiver;
use common\assets\DatePicker;
use common\assets\DocumentTypeValidationAsset;
use common\assets\FontAwesome;
use common\assets\Html5shiv;
use common\assets\MobileFriendlyTablesAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\YiiAsset;
use common\components\AssetManager\AssetBundleManager;





class FrontendAsset extends AssetBundleManager
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/style.css',
        'css/main.css',
        'css/new.css'
    ];

    public $js = [
        'application.js',
        'bus.js',
        'js/jquery.are-you-sure.js',
    ];

    public $depends = [
        ClientErrorReceiver::class,
        YiiAsset::class,
        BootstrapPluginAsset::class,
        FontAwesome::class,
        BootstrapUtils::class,
        Html5shiv::class,
        DatePicker::class,
        MobileFriendlyTablesAsset::class,
        DocumentTypeValidationAsset::class,
    ];
}
