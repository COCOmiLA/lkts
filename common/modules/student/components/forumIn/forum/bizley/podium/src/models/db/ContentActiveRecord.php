<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use yii\helpers\HtmlPurifier;












class ContentActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_content}}';
    }

    


    public function rules()
    {
        return [
            [['content', 'topic'], 'required'],
            [['content', 'topic'], 'string', 'min' => 1],
            ['topic', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
            ['content', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('full'));
            }],
        ];
    }
}
