<?php


namespace common\components\AfterValidateHandler\interfaces;


use yii\db\ActiveRecord;






interface IAfterValidateHandler
{
    



    public function getModel(): ActiveRecord;

    




    public function setModel(ActiveRecord $model): IAfterValidateHandler;
    




    public function isModelValid(): bool;

    



    public function invoke(): IAfterValidateHandler;
}