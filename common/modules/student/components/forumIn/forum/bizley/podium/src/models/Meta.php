<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use cebe\markdown\GithubMarkdown;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\MetaActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;










class Meta extends MetaActiveRecord
{
    const DEFAULT_TIMEZONE = 'UTC';

    


    const MAX_WIDTH  = 165;
    const MAX_HEIGHT = 165;
    const MAX_SIZE   = 204800;

    


    public $image;

    


    public function rules()
    {
        return array_merge(
            parent::rules(),
            [['image', 'image',
                'mimeTypes' => 'image/png, image/jpeg, image/gif',
                'maxWidth' => self::MAX_WIDTH,
                'maxHeight' => self::MAX_HEIGHT,
                'maxSize' => self::MAX_SIZE],
            ]
        );
    }

    




    public function getParsedSignature()
    {
        if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
            $parser = new GithubMarkdown();
            $parser->html5 = true;
            return $parser->parse($this->signature);
        }
        return $this->signature;
    }
}
