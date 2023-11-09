<?php

namespace common\modules\student\components\forumIn\forum\bizley\quill;

use yii\web\AssetBundle;













class QuillAsset extends AssetBundle
{
    



    public $url = 'https://cdn.quilljs.com/';
    
    




    public $version;
    
    


    public $theme;
    
    



    public function registerAssetFiles($view)
    {
        switch ($this->theme) {
            case Quill::THEME_SNOW:
                $this->css = [$this->url . $this->version . '/quill.snow.css'];
                break;
            case Quill::THEME_BUBBLE:
                $this->css = [$this->url . $this->version . '/quill.bubble.css'];
                break;
            default:
                $this->css = [$this->url . $this->version . '/quill.core.css'];
        }
        
        $this->js = [$this->url . $this->version . '/quill.min.js'];
        
        parent::registerAssetFiles($view);
    }
}
