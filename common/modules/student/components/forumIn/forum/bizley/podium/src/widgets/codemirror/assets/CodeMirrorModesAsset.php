<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets;

use yii\web\AssetBundle;







class CodeMirrorModesAsset extends AssetBundle
{
    


    public $sourcePath = '@bower/codemirror/mode';

    


    public $js = [
        'xml/xml.js',
        'javascript/javascript.js',
        'css/css.js',
        'htmlmixed/htmlmixed.js',
        'clike/clike.js',
        'php/php.js',
        'sql/sql.js',
        'meta.js',
        'markdown/markdown.js',
        'gfm/gfm.js',
    ];
}
