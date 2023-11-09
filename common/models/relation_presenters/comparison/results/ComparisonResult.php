<?php

namespace common\models\relation_presenters\comparison\results;

use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\models\relation_presenters\comparison\interfaces\IHaveVirtualPropsToCompare;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\modules\abiturient\models\drafts\IHasRelations;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ComparisonResult extends \yii\base\BaseObject implements IComparisonResult
{
    public $old;
    public $new;
    protected $props_linked_to_parent;
    protected $_result = null;
    protected $_compare_related = true;

    public function __construct($old_value, $new_value, $props_linked_to_parent)
    {
        parent::__construct();
        $this->old = $old_value;
        $this->new = $new_value;
        $this->props_linked_to_parent = $props_linked_to_parent;
    }

    public function setCompareRelated(bool $compareRelated)
    {
        $this->_compare_related = $compareRelated;
        return $this;
    }

    



    public function getResult()
    {
        if (!is_null($this->_result)) {
            return $this->_result;
        }

        $result = [];
        if (!is_null($this->old)) {
            
            $props = array_values(array_unique(array_diff(ArrayHelper::merge(
                EntitiesComparator::getNonRelationProps($this->old),
                EntitiesComparator::getNonRelationProps($this->new)
            ), $this->props_linked_to_parent)));

            foreach ($props as $prop) {
                $result[$prop] = new FieldComparisonResult($this->old, $this->new, $prop);
            }
            if ($this->old instanceof IHaveVirtualPropsToCompare || $this->new instanceof IHaveVirtualPropsToCompare) {
                $old_virtual_props = $this->old->virtualProps ?? [];
                $new_virtual_props = $this->new->virtualProps ?? [];
                $virtual_prop_names = array_values(array_unique(
                    ArrayHelper::merge(
                        array_keys($old_virtual_props),
                        array_keys($new_virtual_props)
                    )
                ));
                foreach ($virtual_prop_names as $virtual_prop_name) {
                    $result[$virtual_prop_name] = new FieldComparisonResult($this->old, $this->new, $virtual_prop_name);
                }
            }

            if ($this->_compare_related && ($this->old instanceof IHasRelations || $this->new instanceof IHasRelations)) {
                $first_relations = ArrayHelper::getValue($this->old, 'relationsInfo', []);
                $second_relations = ArrayHelper::getValue($this->new, 'relationsInfo', []);
                $max_count = max(count($first_relations), count($second_relations));
                for ($i = 0; $i < $max_count; $i++) {
                    $relation = $first_relations[$i] ?? null;
                    if (!$relation) {
                        $relation = $second_relations[$i] ?? null;
                    }
                    if ($relation->ignore_in_comparison) {
                        continue;
                    }
                    $first_children = ArrayHelper::getValue($first_relations, "{$i}.related");
                    $second_children = ArrayHelper::getValue($second_relations, "{$i}.related");
                    if ($relation instanceof OneToOneRelationPresenter) {
                        if (!$first_children) {
                            $first_children = null;
                        }
                        if (!$second_children) {
                            $second_children = null;
                        }
                        $result[$relation->relation_label] = EntitiesComparator::compare(
                            $this->filterArchived($first_children),
                            $this->filterArchived($second_children),
                            $relation->getChildColumnsInvolvedInRelation()
                        );
                    } else {
                        $result[$relation->relation_label] = EntitiesComparator::compareArrays(
                            $this->filterArchived($first_children ?? []),
                            $this->filterArchived($second_children ?? []),
                            $relation->getChildColumnsInvolvedInRelation()
                        );
                    }
                }
            }
        } elseif ($this->new) {
            $result = true;
        }
        $this->_result = $result;
        return $result;
    }

    




    protected function filterArchived($data)
    {
        if (is_array($data)) {
            return array_values(array_filter($data, function ($el) {
                if ($el instanceof ArchiveModelInterface) {
                    return $el->{$el::getArchiveColumn()} != $el::getArchiveValue();
                }
                return true;
            }));
        } else {
            if ($data instanceof ArchiveModelInterface) {
                return $data->{$data::getArchiveColumn()} != $data::getArchiveValue() ? $data : null;
            }
            return $data;
        }
    }

    public function getLeftEntity()
    {
        return $this->old;
    }

    public function getRightEntity()
    {
        return $this->new;
    }

    public function getDifferences(): array
    {
        $return = [];
        $comparison_result = $this->getResult();
        if ($comparison_result) {
            if ($this->old) {
                $entity = $this->old; 
                if (ArrayHelper::isAssociative($comparison_result)) {
                    foreach ($comparison_result as $prop => $prop_result) {
                        if ($prop_result) {
                            if (is_array($prop_result)) {
                                $diffs = array_values(
                                    array_filter(
                                        array_map(
                                            function ($res) {
                                                return $res->getDifferences();
                                            },
                                            $prop_result
                                        ),
                                        function ($res) {
                                            return !!$res;
                                        }
                                    )
                                );
                            } else {
                                $diffs = $prop_result->getDifferences();
                            }
                            if ($diffs) {
                                if ($prop_result instanceof FieldComparisonResult) {
                                    $return = ArrayHelper::merge($return, $diffs);
                                } else {
                                    $return[$entity->getAttributeLabel($prop)] = $diffs;
                                }
                            }
                        }
                    }
                } else {
                    foreach ($comparison_result as $item) {
                        if ($item) {
                            $diffs = $item->getDifferences();
                            if ($diffs) {
                                $return[] = $diffs;
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }
}
