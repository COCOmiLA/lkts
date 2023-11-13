<?php

namespace common\models\relation_presenters\comparison;

use common\models\EmptyCheck;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\comparison\interfaces\IHaveVirtualPropsToCompare;
use common\models\relation_presenters\comparison\results\ComparisonResult;
use common\models\relation_presenters\comparison\results\FieldComparisonResult;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class EntitiesComparator
{
    




    public static function getNestedResult(IComparisonResult $result, string $path)
    {
        $split_path = explode('.', $path);
        $cur_val = $result;
        foreach ($split_path as $step) {
            if ($cur_val instanceof IComparisonResult) {
                $cur_val = $cur_val->getResult();
            }
            $cur_val = ArrayHelper::getValue($cur_val, $step);
        }
        return $cur_val;
    }

    public static function hasDifferences(ActiveRecord $first, ActiveRecord $second, bool $compare_related = true): bool
    {
        $compare_result = EntitiesComparator::compare($first, $second);

        return EntitiesComparator::hasDifferencesInResult(
            $compare_result
                ->setCompareRelated($compare_related)
        );
    }

    




    public static function getComparisonForModel(IHaveIdentityProp $model, array $diffs): ?IComparisonResult
    {
        $model_identity = EntitiesComparator::getIdentityString($model);
        foreach ($diffs as $diff) {
            if ($diff->getRightEntity() && EntitiesComparator::getIdentityString($diff->getRightEntity()) == $model_identity) {
                return $diff;
            }
        }
        return null;
    }

    public static function hasDifferencesInResult(IComparisonResult $result)
    {
        $real_result = $result->getResult();
        if (is_bool($real_result)) {
            return $real_result;
        }
        
        uasort($real_result, function ($a, $b) {
            
            if (is_array($a)) {
                return 1;
            }
            if (is_array($b)) {
                return -1;
            }

            $a_class = get_class($a);
            $b_class = get_class($b);
            if ($a_class == $b_class) {
                return 0;
            }
            return ($a_class instanceof FieldComparisonResult) ? -1 : 1;
        });

        foreach ($real_result as $prop_name => $prop_result) {
            if (is_array($prop_result) && !ArrayHelper::isAssociative($prop_result)) {
                foreach ($prop_result as $res) {
                    if (EntitiesComparator::hasDifferencesInResult($res)) {
                        return true;
                    }
                }
            } else {
                if (EntitiesComparator::hasDifferencesInResult($prop_result)) {

                    return true;
                }
            }
        }

        return false;
    }

    public static function compare(?ActiveRecord $first, ?ActiveRecord $second, array $props_linked_to_parent = []): ComparisonResult
    {
        return new ComparisonResult($first, $second, $props_linked_to_parent);
    }

    public static function compareArrays(array $left_models, array $right_models, array $props_to_parent = []): array
    {
        $left_models = EntitiesComparator::indexByIdentityProps($left_models);
        $right_models = EntitiesComparator::indexByIdentityProps($right_models);
        $result = [];
        foreach ($right_models as $key => $right_model) {
            $result[$key] = EntitiesComparator::compare(($left_models[$key] ?? null), $right_model, $props_to_parent);
        }
        foreach ($left_models as $key => $left_model) {
            if (isset($result[$key])) {
                continue;
            }
            $result[$key] = EntitiesComparator::compare($left_model, ($right_models[$key] ?? null), $props_to_parent);
        }
        return array_values($result);
    }

    public static function indexByIdentityProps(array $models): array
    {
        return ArrayHelper::index($models, function ($item) {
            return EntitiesComparator::getIdentityString($item);
        });
    }

    public static function sortById(array $models): array
    {
        usort($models, function ($l, $r) {
            if ($l->id == $r->id) {
                return 0;
            }
            return ($l->id < $r->id) ? -1 : 1;
        });
        return array_values($models);
    }

    public static function getIdentityString(IHaveIdentityProp $model): string
    {
        $identityString = $model->getIdentityString();
        if ($model instanceof ArchiveModelInterface) {
            
            $archive_prop = ($model->isArchive() ? $model->{$model::getArchivedAtColumn()} : 'not_archive');
            $identityString .= "_{$archive_prop}";
        }
        return md5($identityString);
    }

    




    public static function getExtendedIdentityString(IHaveIdentityProp $model): string
    {
        $archive_prop = null;
        if ($model instanceof ArchiveModelInterface) {
            
            $archive_prop = ($model->isArchive() ? $model->{$model::getArchivedAtColumn()} : 'not_archive');
        }
        $base = "{$model->getIdentityString()}_{$archive_prop}";

        $extends = [];
        $props = EntitiesComparator::getNonRelationProps($model);
        foreach ($props as $prop) {
            $value = EntitiesComparator::getPropertyValue($model, $prop);
            $extends[] = "{$prop}{$value}";
        }
        $extends = implode('', $extends);

        return md5("{$base}{$extends}");
    }

    




    public static function getPropertyValue($model, string $prop)
    {
        if (isset($model[$prop])) {
            return $model[$prop];
        }
        if ($model instanceof IHaveVirtualPropsToCompare) {
            $virtual_props = $model->virtualProps ?? [];
            if (isset($virtual_props[$prop])) {
                return $virtual_props[$prop]($model);
            }
        }

        return null;
    }

    public static function getNonRelationProps(?ActiveRecord $entity): array
    {
        if (!$entity) {
            return [];
        }
        $possible_props_to_compare = [];
        if ($entity instanceof ICanGivePropsToCompare) {
            $possible_props_to_compare = $entity->getPropsToCompare();
        } else {
            $possible_props_to_compare = array_keys($entity->attributes);
        }

        return array_values(array_diff($possible_props_to_compare, EntitiesComparator::getIgnoredProps($entity)));
    }

    public static function getIgnoredProps(ActiveRecord $entity): array
    {
        $result = [
            'id',
            'created_at',
            'updated_at',
        ];
        if ($entity instanceof IHasRelations) {
            foreach ($entity->getRelationsInfo() as $info) {
                $result = ArrayHelper::merge($result, $info->getParentColumnsInvolvedInRelation());
            }
        }
        if ($entity instanceof ArchiveModelInterface) {
            $result[] = $entity::getArchiveColumn();
            $result[] = $entity::getArchivedAtColumn();
        }
        return array_values(array_unique($result));
    }

    public static function renderDifference(?IComparisonResult $mainComparisonResult, array $difference): string
    {
        if ($mainComparisonResult && $difference) {
            
            $leftEntity = ArrayHelper::getValue($mainComparisonResult, 'leftEntity');
            $where_explanation = $leftEntity->translateDraftStatus();
            $changes = trim(EntitiesComparator::differenceToString($difference));
            if ($changes) {
                return Html::tag(
                    'i',
                    null,
                    [
                        'class' => 'fa fa-eye',
                        'style' => 'margin-left: 5px',
                        'data-html' => 'true',
                        'data-toggle' => 'tooltip',
                        'title' => trim("Ранее введённые данные ({$where_explanation}):<hr/>{$changes}"),
                    ]
                );
            }
        }
        return '';
    }

    public static function differenceToString(array $difference): string
    {
        $result = '';
        if (ArrayHelper::isAssociative($difference)) {
            foreach ($difference as $key => $value) {
                $data = $value;
                if ($value instanceof IComparisonResult) {
                    $data = $value->getDifferences();
                }
                if (is_array($data)) {
                    $data = EntitiesComparator::differenceToString($data);
                }
                if (!is_null($data) && !EmptyCheck::isEmpty(trim((string)$data))) {
                    $ends_with_br = str_ends_with($data, '<br/>');
                    $result .= "<span>{$key}:<br/>{$data}</span>";
                    if (!$ends_with_br) {
                        
                        $result .= '<br/>';
                    }
                }
            }
        } else {
            $result = implode(
                '<hr/>',
                array_filter(
                    array_map(function ($item) {
                        $prefix = '';
                        if ($item instanceof IComparisonResult) {
                            if ($item instanceof ComparisonResult) {
                                if (($left = $item->getLeftEntity()) instanceof ICanBeStringified) {
                                    $prefix = "{$left->stringify()}:<br/>";
                                }
                            }
                            $item = $item->getDifferences();
                        }
                        $differenceString = EntitiesComparator::differenceToString($item);
                        if (!EmptyCheck::isEmpty($differenceString)) {
                            return $prefix . $differenceString;
                        }
                        return $differenceString;
                    },
                        $difference
                    ),
                    function (string $change) {
                        return !EmptyCheck::isEmpty($change) && trim((string)$change) != '<br/>';
                    }
                )
            );
        }
        return $result;
    }
}