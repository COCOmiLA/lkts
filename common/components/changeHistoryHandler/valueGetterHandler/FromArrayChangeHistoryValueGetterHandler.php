<?php

namespace common\components\changeHistoryHandler\valueGetterHandler;


class FromArrayChangeHistoryValueGetterHandler extends DefaultChangeHistoryValueGetterHandler
{
    private $entity;

    public function __construct(array $entity)
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

    public function getValue($attr)
    {
        if (isset($this->entity[$attr]) && $this->entity[$attr] instanceof \Closure) {
            return $this->entity[$attr]($this->entity);
        }
        return $this->entity[$attr] ?? null;
    }
}