<?php

namespace common\models\interfaces\dynamic_validation_rules;

interface IHavePropsRelatedToDocumentType extends IHaveDocumentTypeProp
{
    public static function getSubdivisionCodePropertyName(): string;

    public static function getIssuedDatePropertyName(): string;

    public static function getDateOfEndPropertyName(): string;

    public static function getAdditionalPropertyName(): string;

    public static function getIssuedByPropertyName(): string;

    public static function getDocumentSeriesPropertyName(): string;

    public static function getDocumentNumberPropertyName(): string;

    public function ownRequiredRules(): array;
}