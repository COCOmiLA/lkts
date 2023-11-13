<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets;

use yii\web\AssetBundle;







class CodeMirrorExtraAsset extends AssetBundle
{
    


    public $sourcePath = '@bower/codemirror/addon';

    


    public $js = [
        'mode/overlay.js',
        'edit/continuelist.js',
        'fold/xml-fold.js',
        'edit/matchbrackets.js',
        'edit/closebrackets.js',
        'edit/closetag.js',
        'display/panel.js',
    ];
}
