<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets;

use yii\web\AssetBundle;







class CodeMirrorAsset extends AssetBundle
{
    


    public $depends = [
        'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorLibAsset',
        'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorExtraAsset',
        'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorModesAsset',
        'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorButtonsAsset',
        'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorConfigAsset'
    ];
}
