<?php

namespace sguinfocom\widget;


use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\bootstrap4\BootstrapAsset;

/**
 * Bower asset for Bootstrap Tree View
 *
 * @author eXeCUT
 */
class TreeViewBowerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/npm-asset/sgu-bootstrap-treeview/dist';
    public $js = ['bootstrap-treeview.js'];

    public $css = ['bootstrap-treeview.min.css'];

    public $depends = [
        JqueryAsset::class,
        BootstrapAsset::class,
    ];
}
