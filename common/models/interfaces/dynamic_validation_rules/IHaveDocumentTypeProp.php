<?php

namespace common\models\interfaces\dynamic_validation_rules;

interface IHaveDocumentTypeProp
{
    public static function getDocumentTypePropertyName(): string;
}