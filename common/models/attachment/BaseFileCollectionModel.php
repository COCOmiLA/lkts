<?php

namespace common\models\attachment;

use yii\base\Model;









abstract class BaseFileCollectionModel extends Model
{
    


    abstract public function setFormName($formName): void;

    


    abstract public function setSkipOnEmpty($value): void;

    


    abstract public function getSkipOnEmpty(): bool;
}