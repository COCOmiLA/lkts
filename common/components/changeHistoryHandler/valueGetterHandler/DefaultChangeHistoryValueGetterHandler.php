<?php
namespace common\components\changeHistoryHandler\valueGetterHandler;


use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;

class DefaultChangeHistoryValueGetterHandler
{
    


    private $entity;

    public function __construct(ChangeLoggedModelInterface $entity = null)
    {
        $this->entity = $entity;
    }

    


    public function getEntity()
    {
        return $this->entity;
    }

    


    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getValue($attr) {
        $arr = $this->entity->getChangeLoggedAttributes();

        if(array_key_exists($attr, $arr) && $arr[$attr] instanceof \Closure) {
            return $arr[$attr]($this->entity);
        }
        return $this->entity->$attr;
    }
}