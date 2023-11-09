<?php

namespace common\components\dictionaryManager\GetReferencesManager;

use common\components\soapResponse\responses\GetContractorListResponse;

class GetContractorListManager extends GetReferencesManager
{
    protected static function makeReferencesRequest(string $class_name, string $filterType, string $filterText, array $filters): GetContractorListResponse
    {
        return new GetContractorListResponse($class_name, $filterType, $filterText, $filters);
    }
}
