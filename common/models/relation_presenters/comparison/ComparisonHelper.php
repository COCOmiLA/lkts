<?php

namespace common\models\relation_presenters\comparison;

use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class ComparisonHelper extends BaseObject
{
    public const COLORS = [
        'tomato',
        'yellow',
    ];
    public const DEFAULT_DIFFERENCE_CLASS = 'tomato';

    


    public $comparison_entries;
    public $base_path;

    public function __construct($comparison_entries, $property_path)
    {
        parent::__construct();

        
        if (!is_array($comparison_entries)) {
            $comparison_entries = [
                ComparisonHelper::DEFAULT_DIFFERENCE_CLASS => $comparison_entries
            ];
        }
        $this->comparison_entries = $comparison_entries;
        $this->base_path = $property_path;
    }

    public function getRawDifference(string $path = null): array
    {
        $difference = [];
        if ($this->comparison_entries) {
            $full_path = $this->base_path;
            if (!is_null($path)) {
                $full_path .= ".{$path}";
            }
            foreach ($this->comparison_entries as $color => $comparison_entry) {
                if (!$comparison_entry) {
                    continue;
                }
                $result = EntitiesComparator::getNestedResult($comparison_entry, $full_path);
                if ($result) {
                    if ($result instanceof IComparisonResult) {
                        $difference = $result->getDifferences();
                    } else {
                        $difference = $result;
                    }
                    if ($this->containsDifference($difference)) {
                        return [$difference, $color];
                    }
                }
            }
        }
        return [$difference, null];
    }

    protected function containsDifference($difference): bool
    {
        if (!$difference) {
            return false;
        }
        if (!is_array($difference)) {
            $difference = [$difference];
        }
        foreach ($difference as $item) {
            if (is_null($item)) {
                continue;
            }
            if (!($item instanceof IComparisonResult)) {
                return true;
            }
            if (EntitiesComparator::hasDifferencesInResult($item)) {
                return true;
            }
        }
        return false;
    }

    public function getRenderedDifference(string $path = null)
    {
        [$diff, $color] = $this->getRawDifference($path);
        return [EntitiesComparator::renderDifference($this->comparison_entries[$color] ?? null, $diff), $color ? "has_difference_{$color}" : ''];
    }

    public function makeGridViewContentOptionsCallback()
    {
        [$differences, $color] = $this->getRawDifference();
        return function (string $column_name = null, $params = []) use ($differences, $color) {
            return function ($model, $key, $index, $column) use ($params, $differences, $color, $column_name) {
                $result = [];
                $model_comparison = EntitiesComparator::getComparisonForModel($model, $differences);
                if ($model_comparison) {
                    if (!$column_name) {
                        $column_name = $column->attribute;
                    }
                    $field_result = EntitiesComparator::getNestedResult($model_comparison, $column_name);
                    if ($field_result && $field_result->getResult()) {
                        $result['style'] = "background-color: {$color};";
                    }
                }
                return ArrayHelper::merge($params, $result);
            };
        };
    }

    public static function contentOptionsProxyFunc()
    {
        return function ($dummy_string = null, $params = []) {
            return function ($model, $key, $index, $column) use ($params) {
                return $params;
            };
        };
    }

    






    public static function buildComparisonAttributes(
        ?IComparisonResult $comparisonWithActual,
        ?IComparisonResult $comparisonWithSent,
        string             $path
    ): array {
        $class = null;
        $difference = null;
        $comparisonHelper = null;
        if ($comparisonWithSent || $comparisonWithActual) {
            $comparisonHelper = (new ComparisonHelper([
                'yellow' => $comparisonWithSent,
                'tomato' => $comparisonWithActual,
            ], $path));
            [$difference, $class] = $comparisonHelper->getRenderedDifference();
        }

        return [
            'class' => $class,
            'difference' => $difference,
            'comparisonHelper' => $comparisonHelper,
        ];
    }
}
