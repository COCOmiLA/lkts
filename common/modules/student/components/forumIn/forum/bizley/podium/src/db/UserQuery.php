<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\db\ActiveQuery;







class UserQuery extends ActiveQuery
{
    



    public function loggedUser($id)
    {
        if (Podium::getInstance()->userComponent !== true) {
            return $this->andWhere(['inherited_id' => $id]);
        }
        return $this->andWhere(['id' => $id]);
    }
}
