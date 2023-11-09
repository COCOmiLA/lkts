<?php

namespace common\models\notification;






class NotificationTypeQuery extends \yii\db\ActiveQuery
{
    



    public function all($db = null)
    {
        return parent::all($db);
    }

    



    public function one($db = null)
    {
        return parent::one($db);
    }
    
    


    public function enabled()
    {
        return $this->andWhere(['enabled' => 1]);
    }
}
