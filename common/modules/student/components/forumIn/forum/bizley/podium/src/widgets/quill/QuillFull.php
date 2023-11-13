<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\quill;

use common\modules\student\components\forumIn\forum\bizley\quill\Quill;







class QuillFull extends Quill
{
    


    public $toolbarOptions = [
        [['align' => []], ['size' => ['small', false, 'large', 'huge']], 'bold', 'italic', 'underline', 'strike'],
        [['color' => []], ['background' => []]],
        [['header' => [1, 2, 3, 4, 5, 6, false]], ['script' => 'sub'], ['script' => 'super']],
        ['blockquote', 'code-block'],
        [['list' => 'ordered'], ['list' => 'bullet']],
        ['link', 'image', 'video'],
        ['clean']
    ];

    


    public $modules = ['syntax' => true];

    


    public $highlightStyle = 'github-gist.min.css';

    



    public $js = "{quill}.getModule('toolbar').addHandler('image',imageHandler);function imageHandler(){var range=this.quill.getSelection();var value=prompt('URL:');this.quill.insertEmbed(range.index,'image',value,Quill.sources.USER);};";
}
