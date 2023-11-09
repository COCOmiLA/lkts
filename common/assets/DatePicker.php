<?php

namespace common\assets;

use yii\web\AssetBundle;

class DatePicker extends AssetBundle
{
    public $sourcePath = '@bower/eonasdan-bootstrap-datetimepicker';
    public $js = [
        'build/js/bootstrap-datetimepicker.min.js'
    ];

    public $css = [
        'build/css/bootstrap-datetimepicker.min.css'
    ];

    public $depends = [
        \yii\web\YiiAsset::class,
        \yii\bootstrap4\BootstrapAsset::class,
        \common\assets\MomentJs::class
    ];
}

