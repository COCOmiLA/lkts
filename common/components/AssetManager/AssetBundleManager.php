<?php

namespace common\components\AssetManager;

use yii\web\AssetBundle;

class AssetBundleManager extends AssetBundle
{
    public function init()
    {
        parent::init();
        $this->setupAssets();
    }

    


    protected function setupAssets()
    {
        if (getenv('DISABLE_WEBPACK') && getenv('DISABLE_WEBPACK') === 'true') {
            return;
        }
        for ($I = 0; $I < count($this->js); $I++) {
            $this->js[$I] = \common\helpers\Mix::mix($this->js[$I]);
        }
    }
}
