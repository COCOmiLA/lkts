<?php
namespace common\components\maintenance;

use yii\web\AssetBundle;






class MaintenanceAsset extends AssetBundle
{
    public $sourcePath = '@common/components/maintenance/assets';

    public $css = [
        'css/maintenance.css'
    ];

    public $depends = [
        \yii\web\YiiAsset::class,
        \yii\bootstrap4\BootstrapAsset::class
    ];
}
