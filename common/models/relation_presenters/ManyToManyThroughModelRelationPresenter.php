<?php

namespace common\models\relation_presenters;

use common\modules\abiturient\models\drafts\DraftsManager;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ManyToManyThroughModelRelationPresenter extends ManyToManyRelationPresenter
{
    public $via_model;

    public function __construct(string $relation_label, $config)
    {
        parent::__construct($relation_label, $config);
        $this->via_table = $this->via_model::tableName();
    }

    protected function getLinkProviders(ActiveRecord $new_parent, ActiveRecord $old_parent, ActiveRecord $old_child): array
    {
        DraftsManager::ensurePersisted($new_parent);
        [$parent_attrs_provider, $child_attrs_provider, $mediator_provider] = parent::getLinkProviders($new_parent, $old_parent, $old_child);
        $old_junction_records = $this->via_model::find()
            ->andWhere([
                $this->via_table_parent_column => $old_parent->{$this->parent_column_name},
                $this->via_table_child_column => $old_child->{$this->child_column_name},
            ])
            ->all();

        if (!$old_junction_records) {
            throw new NotFoundHttpException('Не удалось найти связующую таблицу');
        }

        $mediator_provider = function (ActiveRecord $child) use ($old_junction_records, $new_parent) {
            DraftsManager::ensurePersisted($child);

            foreach ($old_junction_records as $old_junction_record) {
                $junction_attrs = DraftsManager::excludeIgnoredProps($old_junction_record);
                $junction_data = ArrayHelper::merge(
                    $junction_attrs,
                    [
                        $this->via_table_parent_column => $new_parent->{$this->parent_column_name},
                        $this->via_table_child_column => $child->{$this->child_column_name},
                    ]
                );
                $already_linked = $this->via_model::find()->andWhere($junction_data)->exists();
                if (!$already_linked) {
                    $tmp = new $this->via_model();
                    
                    DraftsManager::setModelAttributes($tmp, $junction_data);
                }
            }
            return [];
        };
        return [$parent_attrs_provider, $child_attrs_provider, $mediator_provider];
    }
}