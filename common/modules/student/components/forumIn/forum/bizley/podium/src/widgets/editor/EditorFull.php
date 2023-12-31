<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\editor;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\codemirror\CodeMirror;
use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\quill\QuillFull;
use yii\widgets\InputWidget;







class EditorFull extends InputWidget
{
    


    public $editor;

    


    public function init()
    {
        parent::init();
        $config = [
            'model' => $this->model,
            'attribute' => $this->attribute,
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options
        ];
        if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
            $config['type'] = 'full';
            $this->editor = new CodeMirror($config);
        } else {
            if (empty($this->options)) {
                $config['options'] = ['style' => 'min-height:320px;'];
            }
            $this->editor = new QuillFull($config);
        }
    }

    


    public function run()
    {
        return $this->editor->run();
    }
}
