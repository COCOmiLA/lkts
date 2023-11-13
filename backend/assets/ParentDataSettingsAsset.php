<?php

namespace backend\assets;

use yii\web\AssetBundle;

class ParentDataSettingsAsset extends AssetBundle
{
    public $sourcePath = __DIR__;

    public $js = ['js/parent-data-settings.js'];

    public $depends = [BackendAsset::class];
}
