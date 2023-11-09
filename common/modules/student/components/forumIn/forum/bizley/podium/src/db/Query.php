<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\db\Query as YiiQuery;







class Query extends YiiQuery
{
    





    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Podium::getInstance()->db;
        }
        return parent::createCommand($db);
    }
}
