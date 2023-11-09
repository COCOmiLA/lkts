<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\quill;

use common\modules\student\components\forumIn\forum\bizley\quill\Quill;







class QuillBasic extends Quill
{
    


    public $toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        [['list' => 'ordered'], ['list' => 'bullet']],
        [['align' => []]],
        ['link']
    ];
}
