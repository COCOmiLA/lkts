<?php




namespace common\rbac\rule;

use yii\rbac\Item;
use yii\rbac\Rule;

class OwnModel extends Rule
{
    
    public $name = 'ownModelRule';

    







    public function execute($user, $item, $params)
    {
        $attribute = isset($params['attribute']) ? $params['attribute'] : 'created_by';
        return $user && isset($params['model']) &&  $user === $params['model']->getAttribute($attribute);
    }
}
