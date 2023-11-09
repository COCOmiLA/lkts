<?php

namespace common\modules\student\components\forumIn\forum\bizley\quill;

use yii\web\AssetBundle;

















class KatexAsset extends AssetBundle
{
    



    public $url = 'https://cdnjs.cloudflare.com/ajax/libs/KaTeX/';
    
    



    public $version;
    
    



    public function registerAssetFiles($view)
    {
        $this->css = [$this->url . $this->version . '/katex.min.css'];
        $this->js = [$this->url . $this->version . '/katex.min.js'];
        
        parent::registerAssetFiles($view);
    }
}
