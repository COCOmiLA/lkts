<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;





class DepdropFixedInheritanceAsset extends AssetBundle
{
    public $basePath = '@webroot';
    
    public $js = [
        'js/depdrop_cospom.min.js'
    ];
    
    public $depends = [JqueryAsset::class];
}
