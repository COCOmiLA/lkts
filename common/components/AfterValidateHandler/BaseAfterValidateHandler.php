<?php


namespace common\components\AfterValidateHandler;

use common\components\AfterValidateHandler\interfaces\IAfterValidateHandler;
use yii\db\ActiveRecord;






class BaseAfterValidateHandler implements IAfterValidateHandler
{
    


    private $model;

    public function isModelValid(): bool
    {
        return empty($this->getModel()->errors);
    }

    public function invoke(): IAfterValidateHandler
    {
        return $this;
    }

    public function getModel(): ActiveRecord
    {
        return $this->model;
    }

    public function setModel(ActiveRecord $model): IAfterValidateHandler
    {
        $this->model = $model;
        return $this;
    }
}