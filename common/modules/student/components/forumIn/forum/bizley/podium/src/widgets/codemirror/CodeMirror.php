<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror;

use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\assets\CodeMirrorAsset;
use Yii;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\InputWidget;







class CodeMirror extends InputWidget
{
    


    public $type = 'basic';

    


    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            if (empty($this->model->{$this->attribute})) {
                $this->model->{$this->attribute} = "\n\n\n\n\n\n\n\n";
            }
            return Html::activeTextarea($this->model, $this->attribute, ['id' => 'codemirror']);
        }
        if (empty($this->value)) {
            $this->value = "\n\n\n\n\n\n\n\n";
        }
        return Html::textarea($this->name, $this->value, ['id' => 'codemirror']);
    }

    



    public function registerClientScript()
    {
        $view = $this->view;
        CodeMirrorAsset::register($view);
        $js = 'var CodeMirrorLabels = {
    bold: "' . Yii::t('podium/view', 'Bold') . '",
    italic: "' . Yii::t('podium/view', 'Italic') . '",
    header: "' . Yii::t('podium/view', 'Header') . '",
    inlinecode: "' . Yii::t('podium/view', 'Inline code') . '",
    blockcode: "' . Yii::t('podium/view', 'Block code') . '",
    quote: "' . Yii::t('podium/view', 'Quote') . '",
    bulletedlist: "' . Yii::t('podium/view', 'Bulleted list') . '",
    orderedlist: "' . Yii::t('podium/view', 'Ordered list') . '",
    link: "' . Yii::t('podium/view', 'Link') . '",
    image: "' . Yii::t('podium/view', 'Image') . '",
    help: "' . Yii::t('podium/view', 'Help') . '",
};var CodeMirrorSet = "' . $this->type . '";';
        $view->registerJs($js, View::POS_BEGIN);
    }
}
