<?php

namespace common\models\relation_presenters;

use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\modules\abiturient\models\drafts\DraftsManager;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

abstract class BaseRelationPresenter extends \yii\base\BaseObject
{
    public $relation_label;
    public $parent_instance;
    public $child_class;
    public $parent_column_name = 'id';
    public $child_column_name;
    public $ignore_in_comparison = false;
    public $find_exists_child = true; 
    public $make_new_child = true; 
    



    public $actual_relation_name = null;
    



    public $props_to_eager_load = null;
    



    public $get_possible_children_callback = null;

    public function __construct(string $relation_label, $config)
    {
        parent::__construct($config);
        $this->relation_label = $relation_label;
    }

    public function getRelated(ActiveRecord $parent = null)
    {
        throw new ErrorException('Needs implementation');
    }

    



    protected function requiredSchemaExists(): bool
    {
        $parent_schema = \Yii::$app->db->getTableSchema($this->parent_instance::tableName());
        $child_schema = \Yii::$app->db->getTableSchema($this->child_class::tableName());
        return $parent_schema !== null
            && $child_schema !== null
            && isset($parent_schema->columns[$this->parent_column_name])
            && isset($child_schema->columns[$this->child_column_name]);
    }

    protected function getLinkProviders(ActiveRecord $new_parent, ActiveRecord $old_parent, ActiveRecord $old_child): array
    {
        return [
            function (ActiveRecord $child) {
                return [];
            },
            function (ActiveRecord $child) {
                return [];
            },
            function (ActiveRecord $child) {
                return [];
            }
        ];
    }

    protected function unlinkUnusedChildren(ActiveRecord $parent, array $touched_children_ids): void
    {
        throw new ErrorException('Needs implementation');
    }

    private function preparePossibleChildren($new_parent_possible_children): array
    {
        if (!$new_parent_possible_children) {
            $new_parent_possible_children = [];
        }
        if (!is_array($new_parent_possible_children)) {
            $new_parent_possible_children = [$new_parent_possible_children];
        }
        return array_map(function (ActiveRecord $child) {
            $child_identity = null;
            if ($child instanceof IHaveIdentityProp) {
                $child_identity = EntitiesComparator::getExtendedIdentityString($child);
            }
            return [$child_identity, $child];
        }, $new_parent_possible_children);
    }

    private static $_memorized_childrens = [];

    private function getRelationHash(): string
    {
        return md5(static::class . $this->relation_label . $this->parent_instance::tableName() . $this->child_class::tableName());
    }

    protected function getPossibleChild(ActiveRecord $new_parent_instance): array
    {
        
        
        
        if ($this->get_possible_children_callback) {
            $hash = $this->getRelationHash();
            if (!isset(self::$_memorized_childrens[$hash])) {
                self::$_memorized_childrens[$hash] = $this->preparePossibleChildren(($this->get_possible_children_callback)($new_parent_instance));
            }
            return self::$_memorized_childrens[$hash];
        }
        $new_parent_possible_children = $this->getRelated($new_parent_instance);
        return $this->preparePossibleChildren($new_parent_possible_children);
    }

    public function copyRelatedRecords(ActiveRecord $new_parent_instance): array
    {
        $result = [];
        if ($this->requiredSchemaExists()) {
            $touched_children_ids = [];
            $old_parent_children = $this->getRelated($this->parent_instance);
            $new_parent_possible_children = [];
            if ($this->find_exists_child) {
                $new_parent_possible_children = $this->getPossibleChild($new_parent_instance);
            }
            if (!$old_parent_children) {
                $old_parent_children = [];
            }
            if (!is_array($old_parent_children)) {
                $old_parent_children = [$old_parent_children];
            }
            foreach ($old_parent_children as $old_child) {
                $child_copy = null;
                if ($this->find_exists_child) {
                    $child_copy = $this->findSuchElementAlreadyCopied($new_parent_possible_children, $old_child, $touched_children_ids);
                }
                [$parent_attrs_provider, $child_attrs_provider, $mediator_provider] = $this->getLinkProviders($new_parent_instance, $this->parent_instance, $old_child);
                if ($this->make_new_child) {
                    
                    $child_copy = DraftsManager::makeCopy($old_child, $child_copy, false, $child_attrs_provider);
                }
                if (!$child_copy) {
                    \Yii::error("Не удалось выполнить копирование: " . print_r($old_child, true), 'drafts');
                    throw new UserException("Произошла ошибка копирования");
                }
                $mediator_provider($child_copy);

                $touched_children_ids[] = DraftsManager::ensurePersisted($child_copy)->id;
                $result = ArrayHelper::merge($result, $parent_attrs_provider($child_copy));
            }
            $this->unlinkUnusedChildren($new_parent_instance, $touched_children_ids);
            
            if ($this->actual_relation_name) {
                unset($new_parent_instance->{$this->actual_relation_name});
            }
        }
        return $result;
    }

    protected function findSuchElementAlreadyCopied(array $new_parent_possible_children, ActiveRecord $old_child, array $ignored_ids): ?ActiveRecord
    {
        if (!($old_child instanceof IHaveIdentityProp)) {
            return null;
        }
        $old_child_identity = EntitiesComparator::getExtendedIdentityString($old_child);
        
        foreach ($new_parent_possible_children as [$new_child_identity, $new_child]) {
            if (
                $new_child_identity === $old_child_identity
                && !in_array($new_child->id, $ignored_ids)
            ) {
                return $new_child;
            }
        }
        return null;
    }

    public function getParentColumnsInvolvedInRelation(): array
    {
        return [$this->parent_column_name];
    }

    public function getChildColumnsInvolvedInRelation(): array
    {
        return [$this->child_column_name];
    }

    protected function getMethodNameByProperty(string $property): string
    {
        return 'get' . ucfirst($property);
    }
}
