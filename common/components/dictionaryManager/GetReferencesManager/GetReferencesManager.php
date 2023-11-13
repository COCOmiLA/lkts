<?php


namespace common\components\dictionaryManager\GetReferencesManager;


use common\components\soapResponse\responses\GetReferencesResponse;
use stdClass;
use Throwable;

class GetReferencesManager
{
    protected const DEFAULT_FILTER = [
        'Operator' => 'And',
    ];

    







    public const FILTER_TYPE_BOL = "BeginningOfLine";

    






    public const FILTER_TYPE_AP = "AnyPosition";

    








    public static function getReferences(string $dictionary, string $filterText = "", string $filterType = self::FILTER_TYPE_AP, array $filters = []): GetReferencesResponse
    {
        
        $filters = array_merge(self::DEFAULT_FILTER, $filters);
        return static::makeReferencesRequest(
            $dictionary,
            $filterType,
            $filterText,
            $filters,
        );
    }

    public static function GetPageSize(): int
    {
        $page_size = getenv('GET_REFERENCE_PAGE_SIZE');
        if ($page_size !== false && is_numeric($page_size)) {
            return (int)$page_size;
        }
        return 1000;
    }

    public static function IsPaginationEnabled(): bool
    {
        try {
            $result = \Yii::$app->dictionaryManager->GetInterfaceVersion('GetReferences');
            
            return version_compare($result, '0.0.18.3') >= 0;
        } catch (Throwable $e) {
            \Yii::error("Не удалось получить версию метода GetReferences: {$e->getMessage()}");
            return false;
        }
    }

    protected static function makeReferencesRequest(string $class_name, string $filterType, string $filterText, array $filters): GetReferencesResponse
    {
        return new GetReferencesResponse($class_name, $filterType, $filterText, $filters);
    }

    public static function addPaginationFilters(array $base_filters, string $class_name, int $pageSize, ?stdClass $lastReference)
    {
        if (!static::IsPaginationEnabled()) {
            return $base_filters;
        }
        $pagination_filters = [
            [
                'Field' => 'AutoOrder',
                'Comparison' => 'Equal',
                'Values' => [
                    'ValueType' => 'Булево',
                    'Value' => false
                ],
            ],
            [
                'Field' => 'Limit',
                'Comparison' => 'Equal',
                'Values' => [
                    'ValueType' => 'Число',
                    'Value' => $pageSize
                ],
            ],
        ];
        if ($lastReference) {
            $pagination_filters[] = [
                'Field' => 'Ссылка',
                'Comparison' => 'Greater',
                'Values' => [
                    'ValueType' => $class_name,
                    'ValueRef' => $lastReference,
                ],
            ];
        }
        return array_merge_recursive(
            $base_filters,
            [
                'SimpleFilters' => $pagination_filters
            ]
        );
    }
}