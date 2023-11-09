<?php

namespace common\components\soapResponse\responses;

use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\components\soapResponse\exceptions\SoapBadRequestException;
use stdClass;
use yii\base\UserException;

class GetReferencesResponse extends BaseResponse
{
    protected $class_name;
    protected $filter_type;
    protected $filter_text;
    protected $filters;
    protected $all_rows_count;

    public function __construct(string $class_name, string $filterType, string $filterText, array $filters)
    {
        $this->class_name = $class_name;
        $this->filter_type = $filterType;
        $this->filter_text = $filterText;
        $this->filters = $filters;
    }

    protected function prepareResponseData($response): array
    {
        if (!$response || !isset($response->return)) {
            throw new SoapBadRequestException($this);
        }
        if (isset($response->return->Error) && $response->return->Error != null) {
            throw new SoapBadRequestException($this, $response->return->Error->Description);
        }
        if (!isset($response->return->References)) {
            $response->return->References = [];
        }
        if (!is_array($response->return->References)) {
            $refs = [$response->return->References];
        } else {
            $refs = $response->return->References;
        }
        return [$refs, (int)$response->return->AllRowsCount];
    }

    public function getAllRowsCount(): int
    {
        if (is_null($this->all_rows_count)) {
            throw new UserException('Для получения количества записей необходимо выполнить первый запрос');
        }
        return $this->all_rows_count;
    }

    protected function buildRequest(?stdClass $lastReference): array
    {
        return [
            'ReferenceClassName' => $this->class_name,
            'TextFilterType' => $this->filter_type,
            'Text' => $this->filter_text,
            'Filters' => GetReferencesManager::addPaginationFilters($this->filters, $this->class_name, GetReferencesManager::GetPageSize(), $lastReference)
        ];
    }

    



    protected function getLastReference(array $result)
    {
        return end($result);        
    }

    public function getReferences(): \Generator
    {
        $lastReference = null;
        $result = [];
        $is_first = true;
        do {
            $response = \Yii::$app->soapClientAbit->load($this->getMethodName(), $this->buildRequest($lastReference));
            [$result, $all_rows_count] = $this->prepareResponseData($response);

            $lastReference = $this->getLastReference($result);
            if ($is_first) {
                $this->all_rows_count = $all_rows_count;
            }

            foreach ($result as $item) {
                yield $item;
            }

            $is_first = false;
        } while (GetReferencesManager::IsPaginationEnabled() && count($result) === GetReferencesManager::GetPageSize());
    }

    public function getMethodName(): string
    {
        return "GetReferences";
    }
}