<?php

namespace common\modules\abiturient\views\bachelor\assets;

use yii\web\AssetBundle;

class OlympiadAsset extends AssetBundle
{
    public $sourcePath = __DIR__;

    public $css = ['css/olympiad.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
