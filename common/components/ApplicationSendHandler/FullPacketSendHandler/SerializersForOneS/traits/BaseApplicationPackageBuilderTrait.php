<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\traits;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseApplicationPackageBuilder;
use yii\helpers\ArrayHelper;

trait BaseApplicationPackageBuilderTrait
{
    

    







    public static function buildAdditionalAttribute(string $attributeName, $attributeValue): array
    {
        return [
            'ElementName' => $attributeName,
            'ElementValue' => $attributeValue,
        ];
    }

    









    public static function convertAdditionalElement($additionalElement): array
    {
        if (!is_array($additionalElement) || ArrayHelper::isAssociative($additionalElement)) {
            $additionalElement = [$additionalElement];
        }

        $result = [];
        foreach ($additionalElement as $element) {
            if (isset($element['ElementValue']['enc_value'])) {
                $element['ElementValue'] = $element['ElementValue']['enc_value'];
            }

            $result[$element['ElementName']] = $element['ElementValue'];
        }

        return $result;
    }
}
