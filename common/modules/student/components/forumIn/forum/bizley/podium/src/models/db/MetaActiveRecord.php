<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\behaviors\TimestampBehavior;
use yii\helpers\HtmlPurifier;


















class MetaActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_user_meta}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [['location', 'signature'], 'trim'],
            ['location', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
            ['gravatar', 'boolean'],
            ['signature', 'filter', 'filter' => function ($value) {
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('markdown'));
                }
                return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig());
            }],
            ['signature', 'string', 'max' => 512],
            ['timezone', 'match', 'pattern' => '/[\w\-]+/'],
            ['anonymous', 'boolean'],
        ];
    }
}
