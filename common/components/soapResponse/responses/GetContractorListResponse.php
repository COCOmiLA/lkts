<?php

namespace common\components\soapResponse\responses;

use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\components\soapResponse\exceptions\SoapBadRequestException;
use stdClass;

class GetContractorListResponse extends GetReferencesResponse
{
    protected function prepareResponseData($response): array
    {
        if (!$response || !isset($response->return)) {
            throw new SoapBadRequestException($this);
        }
        if (isset($response->return->Error) && $response->return->Error != null) {
            throw new SoapBadRequestException($this, $response->return->Error->Description);
        }
        if (!isset($response->return->Contractors)) {
            $response->return->Contractors = [];
        }
        if (!is_array($response->return->Contractors)) {
            $refs = [$response->return->Contractors];
        } else {
            $refs = $response->return->Contractors;
        }
        return [$refs, (int)$response->return->AllRowsCount];
    }
    
    protected function buildRequest(?stdClass $lastReference): array
    {
        return [
            'TextFilterType' => $this->filter_type,
            'Text' => $this->filter_text,
            'Filters' => GetReferencesManager::addPaginationFilters($this->filters, $this->class_name, GetReferencesManager::GetPageSize(), $lastReference)
        ];
    }

    



    protected function getLastReference(array $result)
    {
        $lastElement = end($result);
        return $lastElement->ContractorRef ?? null;
    }

    public function getMethodName(): string
    {
        return 'GetContractorList';
    }
}
