<?php

namespace common\components\imageCropper\assets;

use yii\web\AssetBundle;




class CropperAsset extends AssetBundle
{
    


    public $sourcePath = '@common/components/imageCropper/web/';

    


    public $css = [
        'css/cropper.css'
    ];

    


    public $js = [
        'js/cropper.js'
    ];

    


    public $depends = [
        'yii\web\JqueryAsset',
        'common\components\imageCropper\assets\JcropAsset',
        'common\components\imageCropper\assets\SimpleAjaxUploaderAsset',
    ];
}
