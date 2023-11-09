<?php
namespace common\components\queries;

use yii\db\ActiveQuery;

class AttachmentQuery extends ActiveQuery
{
    



    public function notInRegulation(): ActiveQuery
    {
        return $this
            ->leftJoin('regulation r', 'r.attachment_type = at.id')
            ->andWhere(['r.id' => null]);
    }
}