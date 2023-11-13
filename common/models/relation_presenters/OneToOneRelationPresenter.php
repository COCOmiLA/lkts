<?php

namespace common\models\relation_presenters;

use common\modules\abiturient\models\drafts\DraftsManager;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class OneToOneRelationPresenter extends BaseRelationPresenter
{
    public function getRelated(ActiveRecord $parent = null): ?ActiveRecord
    {
        if (!$this->requiredSchemaExists()) {
            return null;
        }
        if (is_null($parent)) {
            $parent = $this->parent_instance;
        }
        if ($parent->getIsNewRecord()) {
            return null;
        }
        if ($this->actual_relation_name && !$this->props_to_eager_load) {
            return $parent->{$this->actual_relation_name};
        } elseif ($this->actual_relation_name && $this->props_to_eager_load) {
            $method = $this->getMethodNameByProperty($this->actual_relation_name);
            return $parent->{$method}()->with($this->props_to_eager_load)->one();
        }
        
        $query = $this->child_class::find();
        if ($this->props_to_eager_load) {
            $query = $query->with($this->props_to_eager_load);
        }
        return $query
            ->andWhere([$this->child_column_name => $parent->{$this->parent_column_name}])
            ->one();
    }

    protected function getLinkProviders(ActiveRecord $new_parent, ActiveRecord $old_parent, ActiveRecord $old_child): array
    {
        [$parent_attrs_provider, $child_attrs_provider, $mediator_provider] = parent::getLinkProviders($new_parent, $old_parent, $old_child);
        if ($this->child_column_name === 'id') {
            $parent_attrs_provider = function (ActiveRecord $child) {
                DraftsManager::ensurePersisted($child);
                return [$this->parent_column_name => $child->{$this->child_column_name}];
            };
        } else {
            DraftsManager::ensurePersisted($new_parent);
            $child_attrs_provider = function (ActiveRecord $child) use ($new_parent) {
                return [$this->child_column_name => $new_parent->{$this->parent_column_name}];
            };
        }
        return [$parent_attrs_provider, $child_attrs_provider, $mediator_provider];
    }

    protected function unlinkUnusedChildren(ActiveRecord $parent, array $touched_children_ids): void
    {
        if (!$this->requiredSchemaExists()) {
            return;
        }
        if ($this->child_column_name !== 'id') {
            if ($parent->getIsNewRecord()) {
                return;
            }
            if ($this->actual_relation_name) {
                $method = $this->getMethodNameByProperty($this->actual_relation_name);
                $query = $parent->{$method}();
            } else {
                $query = $this->child_class::find()
                    ->andWhere([$this->child_column_name => $parent->{$this->parent_column_name}]);
            }
            $old_children = $query
                ->andWhere(['not', ['id' => $touched_children_ids]])
                ->all();

            foreach ($old_children as $old_child) {
                DraftsManager::SuspendHistory($old_child);
                $old_child->delete();
            }
        } else {
            if (!in_array($parent->{$this->parent_column_name}, $touched_children_ids)) {
                $parent->{$this->parent_column_name} = null;
                if ($parent->getIsNewRecord()) {
                    return;
                }
                DraftsManager::SuspendHistory($parent);

                $parent->save(false, [$this->parent_column_name]);
            }
        }
    }
}