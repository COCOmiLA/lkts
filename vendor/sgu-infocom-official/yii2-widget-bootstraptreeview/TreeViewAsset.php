<?php

namespace sguinfocom\widget;

use execut\yii\web\AssetBundle;

/**
 * Custom styles
 *
 * @author eXeCUT
 */
class TreeViewAsset extends AssetBundle
{
    public $depends = [
        TreeViewBowerAsset::class,
    ];
}
