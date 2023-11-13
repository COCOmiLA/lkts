<?php

namespace common\models\relation_presenters;

use common\components\AttachmentManager;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use yii\db\ActiveRecord;

class AttachmentsRelationPresenter extends BaseRelationPresenter
{
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
        if ($this->actual_relation_name) {
            return $parent->{$this->actual_relation_name};
        }
        $table = Attachment::tableName();
        
        $query = Attachment::find()
            ->joinWith(['userRegulation ur'])
            ->andWhere(['ur.id' => null]);
        $questionary = null;
        if ($parent instanceof AbiturientQuestionary) {
            $questionary = $parent;
        }
        if ($questionary) {
            $query->andWhere(["{$table}.questionary_id" => $questionary->id]);
        }
        $application = $this->getApplication($parent);
        if ($application) {
            $query->andWhere(["{$table}.application_id" => $application->id]);
        }
        if ($parent instanceof AttachmentLinkableEntity) {
            $junction_table = $parent::getTableLink();
            $attachment_link_column = $parent::getAttachmentTableLinkAttribute();
            $link_column = $parent::getEntityTableLinkAttribute();
            $query->innerJoin($junction_table, "{$junction_table}.{$attachment_link_column} = {$table}.id");
            $query->andWhere(["{$junction_table}.{$link_column}" => $parent->id]);
        } else {
            
            $query->innerJoinWith('attachmentType at');
            $query->andWhere([
                'not',
                [
                    "at.system_type" => Attachment::getLinkableSystemTypes()
                ]]);
        }
        return $query->with(['linkedFile'])->all();
    }

    protected function requiredSchemaExists(): bool
    {
        return \Yii::$app->db->getTableSchema(Attachment::tableName()) !== null
            && \Yii::$app->db->getTableSchema(UserRegulation::getTableLink()) !== null
            && (!($this->parent_instance instanceof AttachmentLinkableEntity) || \Yii::$app->db->getTableSchema($this->parent_instance::getTableLink()) !== null);
    }

    protected function getLinkProviders(ActiveRecord $new_parent, ActiveRecord $old_parent, ActiveRecord $old_child): array
    {
        
        DraftsManager::ensurePersisted($new_parent);
        [$parent_attrs_provider, $child_attrs_provider, $mediator_provider] = parent::getLinkProviders($new_parent, $old_parent, $old_child);
        $child_attrs_provider = function (ActiveRecord $child) use ($new_parent) {
            $questionary = null;
            if ($new_parent instanceof AbiturientQuestionary) {
                $questionary = $new_parent;
            }
            $application = $this->getApplication($new_parent);
            return [
                'application_id' => $application->id ?? null,
                'questionary_id' => $questionary->id ?? null,
            ];
        };

        $mediator_provider = function (ActiveRecord $child) use ($new_parent) {
            if ($new_parent instanceof AttachmentLinkableEntity) {
                AttachmentManager::linkAttachment($new_parent, $child);
            }
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

        if ($parent instanceof AbiturientQuestionary) {
            $query = Attachment::find()
                ->joinWith(['userRegulation ur'])
                ->andWhere(['ur.id' => null])
                ->andWhere([Attachment::tableName() . '.questionary_id' => $parent->id])
                ->andWhere(['not', [Attachment::tableName() . '.id' => $touched_children_ids]]);
            foreach ($query->all() as $attach) {
                DraftsManager::SuspendHistory($attach);
                $attach->delete();
            }
        }
        if ($parent instanceof BachelorApplication) {
            $query = Attachment::find()
                ->joinWith(['userRegulation ur'])
                ->andWhere(['ur.id' => null])
                ->andWhere([Attachment::tableName() . '.application_id' => $parent->id])
                ->innerJoinWith('attachmentType at')
                ->andWhere(
                    [
                        "at.system_type" => AttachmentType::SYSTEM_TYPE_COMMON
                    ])
                ->andWhere(['not', [Attachment::tableName() . '.id' => $touched_children_ids]]);
            foreach ($query->all() as $attach) {
                DraftsManager::SuspendHistory($attach);
                $attach->delete();
            }
        }
        if ($parent instanceof AttachmentLinkableEntity) {
            $query = $parent->getAttachments()
                ->andWhere(['not', [Attachment::tableName() . '.id' => $touched_children_ids]]);
            foreach ($query->all() as $attach) {
                DraftsManager::SuspendHistory($attach);
                $attach->delete();
            }
        }
    }

    private function getApplication($model): ?BachelorApplication
    {
        $application = null;
        if ($model instanceof BachelorApplication) {
            $application = $model;
        } elseif ($model instanceof ApplicationConnectedInterface) {
            $application = $model->application;
        }
        return $application;
    }

    public function getChildColumnsInvolvedInRelation(): array
    {
        return ['questionary_id', 'application_id'];
    }
}