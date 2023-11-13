<?php

namespace common\models\relation_presenters;

use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\modules\abiturient\models\drafts\DraftsManager;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

class ManyToManyRelationPresenter extends BaseRelationPresenter
{
    public $via_table;
    public $via_table_parent_column;
    public $via_table_child_column;

    public function __construct(string $relation_label, $config)
    {
        parent::__construct($relation_label, $config);
        if (!$this->ignore_in_comparison && !((new $this->child_class()) instanceof IHaveIdentityProp)) {
            throw new \RuntimeException("Class {$this->child_class} needs IHaveIdentityProp implementation");
        }
    }

    public function getRelated(ActiveRecord $parent = null): array
    {
        if (!$this->requiredSchemaExists()) {
            return [];
        }
        if (is_null($parent)) {
            $parent = $this->parent_instance;
        }
        if ($parent->getIsNewRecord()) {
            return [];
        }
        if ($this->actual_relation_name && !$this->props_to_eager_load) {
            return $parent->{$this->actual_relation_name};
        } elseif ($this->actual_relation_name && $this->props_to_eager_load) {
            $method = $this->getMethodNameByProperty($this->actual_relation_name);
            return $parent->{$method}()->with($this->props_to_eager_load)->all();
        }
        
        $query = $this->child_class::find();

        $child_table_name = $this->child_class::tableName();
        $parent_class = get_class($parent);
        $parent_table_name = $parent_class::tableName();

        $query->innerJoin(['junction' => $this->via_table], "junction.{$this->via_table_child_column} = {$child_table_name}.{$this->child_column_name}");
        $query->innerJoin($parent_table_name, "{$parent_table_name}.{$this->parent_column_name} = junction.{$this->via_table_parent_column}");
        if ($this->props_to_eager_load) {
            $query = $query->with($this->props_to_eager_load);
        }
        return $query
            ->andWhere(["{$parent_table_name}.{$this->parent_column_name}" => $parent->{$this->parent_column_name}])
            ->all();
    }

    protected function requiredSchemaExists(): bool
    {
        $parent_schema = \Yii::$app->db->getTableSchema($this->parent_instance::tableName());
        $child_schema = \Yii::$app->db->getTableSchema($this->child_class::tableName());
        $via_table_schema = \Yii::$app->db->getTableSchema($this->via_table);

        return $parent_schema !== null
            && $child_schema !== null
            && $via_table_schema !== null
            && isset($parent_schema->columns[$this->parent_column_name])
            && isset($child_schema->columns[$this->child_column_name])
            && isset($via_table_schema->columns[$this->via_table_parent_column])
            && isset($via_table_schema->columns[$this->via_table_child_column]);
    }

    protected function getLinkProviders(ActiveRecord $new_parent, ActiveRecord $old_parent, ActiveRecord $old_child): array
    {
        DraftsManager::ensurePersisted($new_parent);
        [$parent_attrs_provider, $child_attrs_provider, $mediator_provider] = parent::getLinkProviders($new_parent, $old_parent, $old_child);
        $mediator_provider = function (ActiveRecord $child) use ($new_parent) {
            DraftsManager::ensurePersisted($child);
            $junction_data = [
                $this->via_table_parent_column => $new_parent->{$this->parent_column_name},
                $this->via_table_child_column => $child->{$this->child_column_name},
            ];
            $already_linked = (new Query())->from($this->via_table)->andWhere($junction_data)->exists();
            if (!$already_linked) {
                \Yii::$app->db->createCommand()->insert($this->via_table, $junction_data)->execute();
            }
            return [];
        };
        return [$parent_attrs_provider, $child_attrs_provider, $mediator_provider];
    }


    protected function unlinkUnusedChildren(ActiveRecord $parent, array $touched_children_ids): void
    {
        if (!$this->requiredSchemaExists()) {
            return;
        }
        if ($parent->getIsNewRecord()) {
            return;
        }
        $child_table_name = $this->child_class::tableName();

        if ($this->actual_relation_name) {
            $method = $this->getMethodNameByProperty($this->actual_relation_name);
            $query = $parent->{$method}();
        } else {
            
            $query = $this->child_class::find()
                ->innerJoin(['junction' => $this->via_table], "junction.{$this->via_table_child_column} = {$child_table_name}.{$this->child_column_name}")
                ->andWhere(["junction.{$this->via_table_parent_column}" => $parent->{$this->parent_column_name}]);
        }
        $query = $query
            ->andWhere(['not', ["{$child_table_name}.{$this->child_column_name}" => $touched_children_ids]]);

        foreach ($query->all() as $old_related) {
            DraftsManager::SuspendHistory($old_related);
            $old_related->delete();
        }
        \Yii::$app->db->createCommand()->delete($this->via_table, [
            'and',
            [$this->via_table_parent_column => $parent->{$this->parent_column_name}],
            ['not', [$this->via_table_child_column => $touched_children_ids]]
        ])->execute();
    }
}