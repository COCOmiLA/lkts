<?php

namespace common\modules\student\components\forumIn\forum\bizley\quill;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\InputWidget;






















class Quill extends InputWidget
{
    const THEME_SNOW = 'snow';
    const THEME_BUBBLE = 'bubble';
    
    







    public $theme = self::THEME_SNOW;
    
    const TOOLBAR_FULL = 'FULL';
    const TOOLBAR_BASIC = 'BASIC';
    
    






    public $toolbarOptions = true;
    
    





    public $placeholder;
    
    






    public $bounds;
    
    





    public $debug;
    
    





    public $formats;
    
    





    public $modules;
    
    





    public $readOnly;
    
    




    public $js;
    
    




    public $quillVersion = '1.2.0';
    
    





    public $configuration;
    
    




    public $katexVersion = '0.7.1';
    
    




    public $highlightVersion = '9.9.0';
    
    





    public $highlightStyle = 'default.min.css';
    
    



    public $options = ['style' => 'min-height:150px;'];
    
    



    public $tag = 'div';

    


    public static $autoIdPrefix = 'quill-';
    
    


    protected $_fieldId;
    
    



    protected $_quillConfiguration = [];

    


    public function init()
    {
        if (empty($this->quillVersion) && !is_string($this->quillVersion)) {
            throw new InvalidConfigException('The "quillVersion" property must be a non-empty string!');
        }
        if (!empty($this->configuration) && !is_array($this->configuration)) {
            throw new InvalidConfigException('The "configuration" property must be an array!');
        }
        if (!empty($this->js) && !is_string($this->js)) {
            throw new InvalidConfigException('The "js" property must be a string!');
        }
        if (!empty($this->formats) && !is_array($this->formats)) {
            throw new InvalidConfigException('The "formats" property must be an array!');
        }
        if (!empty($this->modules) && !is_array($this->modules)) {
            throw new InvalidConfigException('The "modules" property must be an array!');
        }
        
        parent::init();
        
        $this->_fieldId = $this->options['id'];
        $this->options['id'] = 'editor-' . $this->id;
        
        $this->prepareOptions();
    }
    
    private $_katex = false;
    private $_highlight = false;
    
    


    protected function prepareOptions()
    {
        if (!empty($this->configuration)) {
            if (isset($this->configuration['theme'])) {
                $this->theme = $this->configuration['theme'];
            }
            if (isset($this->configuration['modules']['formula'])) {
                $this->_katex = true;
            }
            if (isset($this->configuration['modules']['syntax'])) {
                $this->_highlight = true;
            }
            $this->_quillConfiguration = $this->configuration;
        } else {
            if (!empty($this->theme)) {
                $this->_quillConfiguration['theme'] = $this->theme;
            }
            if (!empty($this->bounds)) {
                $this->_quillConfiguration['bounds'] = new JsExpression($this->bounds);
            }
            if (!empty($this->debug)) {
                $this->_quillConfiguration['debug'] = $this->debug;
            }
            if (!empty($this->placeholder)) {
                $this->_quillConfiguration['placeholder'] = $this->placeholder;
            }
            if (!empty($this->formats)) {
                $this->_quillConfiguration['formates'] = $this->formats;
            }
            
            if (!empty($this->modules)) {
                foreach ($this->modules as $module => $config) {
                    $this->_quillConfiguration['modules'][$module] = $config;
                    if ($module == 'formula') {
                        $this->_katex = true;
                    }
                    if ($module == 'syntax') {
                        $this->_highlight = true;
                    }
                }
            }
            if (!empty($this->toolbarOptions)) {
                $this->_quillConfiguration['modules']['toolbar'] = $this->renderToolbar();
            }
        }
    }
    
    


    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            return Html::activeHiddenInput(
                $this->model, $this->attribute, ['id' => $this->_fieldId]
            ) . Html::tag(
                $this->tag, $this->model->{$this->attribute}, $this->options
            );
        }
        return Html::hiddenInput(
            $this->name, $this->value, ['id' => $this->_fieldId]
        ) . Html::tag(
            $this->tag, $this->value, $this->options
        );
    }
    
    



    public function registerClientScript()
    {
        $view = $this->view;
        
        if ($this->_katex) {
            $katexAsset = KatexAsset::register($view);
            $katexAsset->version = $this->katexVersion;
        }
        if ($this->_highlight) {
            $highlightAsset = HighlightAsset::register($view);
            $highlightAsset->version = $this->highlightVersion;
            $highlightAsset->style = $this->highlightStyle;
        }
        
        $asset = QuillAsset::register($view);
        $asset->theme = $this->theme;
        $asset->version = $this->quillVersion;
        
        $configs = Json::encode($this->_quillConfiguration);
        $editor = 'q_' . preg_replace('~[^0-9_\p{L}]~u', '_', $this->id);
        
        $js = "var $editor=new Quill(\"#editor-{$this->id}\",$configs);";
        $js .= "document.getElementById(\"editor-{$this->id}\").onclick=function(e){document.querySelector(\"#editor-{$this->id} .ql-editor\").focus();};";
        $js .= "$editor.on('text-change',function(){document.getElementById(\"{$this->_fieldId}\").value=$editor.root.innerHTML;});";
        if (!empty($this->js)) {
            $js .= str_replace('{quill}', $editor, $this->js);
        }
        $view->registerJs($js, View::POS_END);
    }
    
    



    public function renderToolbar()
    {
        if ($this->toolbarOptions == self::TOOLBAR_BASIC) {
            return [
                ['bold', 'italic', 'underline', 'strike'], 
                [['list' => 'ordered'], ['list' => 'bullet']], 
                [['align' => []]], 
                ['link']
            ];
        }
        if ($this->toolbarOptions == self::TOOLBAR_FULL) {
            return [
                [['font' => []], ['size' => ['small', false, 'large', 'huge']]],
                ['bold', 'italic', 'underline', 'strike'],
                [['color' => []], ['background' => []]],
                [['script' => 'sub'], ['script' => 'super']],
                [['header' => 1], ['header' => 2], 'blockquote', 'code-block'],
                [['list' => 'ordered'], ['list' => 'bullet'], ['indent' => '-1'], ['indent' => '+1']],
                [['direction' => 'rtl'], ['align' => []]],
                ['link', 'image', 'video'],
                ['clean']
            ];
        }
        return $this->toolbarOptions;
    }
}
