<?php

namespace common\modules\student\components\forumIn\forum\bizley\quill;

use yii\web\AssetBundle;

















class HighlightAsset extends AssetBundle
{
    



    public $url = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/';
    
    



    public $version;
    
    



    public $style;
    
    



    public function registerAssetFiles($view)
    {
        $this->css = [$this->url . $this->version . '/styles/' . $this->style];
        $this->js = [$this->url . $this->version . '/highlight.min.js'];
        
        parent::registerAssetFiles($view);
    }
}
