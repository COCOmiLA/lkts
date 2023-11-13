<?php

namespace common\models;

use common\components\changeHistoryHandler\ApplicationActiveRecordChangeHistoryHandler;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\changeHistoryHandler\QuestionaryActiveRecordChangeHistoryHandler;
use common\components\changeHistoryHandler\valueGetterHandler\DefaultChangeHistoryValueGetterHandler;
use common\components\RegulationRelationManager;
use common\models\attachment\attachmentCollection\ActiveFormAttachmentCollection;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\FileToShowInterface;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\ManyToManyRelationPresenter;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;























final class UserRegulation extends ActiveRecord
    implements
    ApplicationConnectedInterface,
    QuestionaryConnectedInterface,
    ChangeLoggedModelInterface,
    IHasRelations,
    IHaveIdentityProp,
    AttachmentLinkableEntity,
    ICanBeStringified
{
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    


    private $rawAttachment;
    private $rawOwner;

    private $index;

    


    private $_historyValueGetter;

    


    private $_changeHistoryHandler;
    protected bool $_new_record = true;

    


    public static function tableName()
    {
        return '{{%user_regulation}}';
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    


    public function rules()
    {
        return [
            [['owner_id', 'regulation_id', 'application_id'], 'integer'],
            [['is_confirmed',], 'boolean'],
            ['is_confirmed', 'required', 'requiredValue' => 1, 'message' => \Yii::t(
                'abiturient/bachelor/questionary/user-regilations',
                'Сообщение о необходимости подтвердить ознакомление: `Необходимо подтвердить прочтение нормативного документа.`'
            ), 'when' => function (UserRegulation $model) {
                return (bool)$model->regulation->confirm_required;
            }],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['owner_id' => 'id']],
            [['regulation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Regulation::class, 'targetAttribute' => ['regulation_id' => 'id']],
            [['application_id'], 'exist', 'skipOnError' => true, 'targetClass' => BachelorApplication::class, 'targetAttribute' => ['application_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'regulation_id' => 'Нормативный документ',
            'is_confirmed' => 'Is Confirmed',
        ];
    }

    




    public function getAttachments(): ActiveQuery
    {
        return $this->getRawAttachments()
            ->andOnCondition([Attachment::tableName() . '.deleted' => false]);
    }

    public function getRawAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['id' => 'attachment_id'])
            ->viaTable(UserRegulation::getTableLink(), ['user_regulation_id' => 'id']);
    }

    




    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    




    public function getRegulation()
    {
        return $this->hasOne(Regulation::class, ['id' => 'regulation_id']);
    }

    




    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'abiturient_questionary_id']);
    }

    public function setRawAttachment(Attachment $attachment): void
    {
        $this->rawAttachment = $attachment;
    }

    public function setRawOwner(User $owner): void
    {
        $this->rawOwner = $owner;
    }

    


    public function getComputedAttachments(): array
    {
        return $this->getAttachments() !== null ? $this->getAttachments()->all() : [$this->rawAttachment];
    }

    public function getAttachmentCollection(): ?FileToShowInterface
    {
        if ($this->regulation->attachmentType !== null) {
            $class = $this->getAttachmentCollectionClass();

            return new $class($this->regulation->attachmentType, $this->getConnectedEntity(), $this->getComputedAttachments());
        }

        return null;
    }


    public function inQuestionary(): bool
    {
        return in_array($this->regulation->related_entity, [
            RegulationRelationManager::RELATED_ENTITY_QUESTIONARY,
            RegulationRelationManager::RELATED_ENTITY_REGISTRATION,
        ], true);
    }

    


    public function getAttachmentCollectionClass(): string
    {
        return ActiveFormAttachmentCollection::class;
    }

    public function getConnectedEntity()
    {
        return $this->owner ?? $this->rawOwner;
    }

    public function getIndex()
    {
        return $this->regulation_id;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'regulation_id' => function ($model) {
                return $model->regulation === null ? null : $model->regulation->name;
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_USER_REGULATION;
    }

    


    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler): void
    {
        $this->_changeHistoryHandler = $handler;
    }

    


    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        $this->setUpChangeHistoryHandler();
        return $this->_changeHistoryHandler;
    }

    protected function setUpChangeHistoryHandler()
    {
        if ($this->_changeHistoryHandler === null && $this->regulation) {
            if ($this->inQuestionary()) {
                $this->setChangeHistoryHandler(new QuestionaryActiveRecordChangeHistoryHandler($this));
            } else {
                $this->setChangeHistoryHandler(new ApplicationActiveRecordChangeHistoryHandler($this));
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->getChangeHistoryHandler()
                ->getInsertHistoryAction()
                ->proceed();
        }
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return new static();
    }

    public function getEntityIdentifier(): ?string
    {
        return null;
    }


    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $attachments = $this->rawAttachments;
        foreach ($attachments as $attachment) {
            $attachment->setScenario(Attachment::SCENARIO_MARK_DELETED);
            $this->unlink('rawAttachments', $attachment, true);
            $attachment->delete();
        }
        return true;
    }

    public function getRelationsInfo(): array
    {
        return [
            
            new ManyToManyRelationPresenter('attachments', [
                'parent_instance' => $this,
                'child_class' => Attachment::class,
                'parent_column_name' => 'id',
                'child_column_name' => 'id',
                'via_table' => UserRegulation::getTableLink(),
                'via_table_parent_column' => 'user_regulation_id',
                'via_table_child_column' => 'attachment_id',
            ])
        ];
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT;
    }

    public function getIdentityString(): string
    {
        $is_confirmed = (int)$this->is_confirmed;
        $user_fio = ArrayHelper::getValue($this, 'owner.fullName');
        $regulation_name = ArrayHelper::getValue($this, 'regulation.name');
        return "{$user_fio}_{$regulation_name}_{$is_confirmed}";
    }

    public static function getTableLink(): string
    {
        return '{{%attachments-user_regulations}}';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'user_regulation_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return 'attachment_id';
    }

    public static function getModel(): string
    {
        return get_called_class();
    }

    public static function getDbTableSchema(): TableSchema
    {
        return UserRegulation::getTableSchema();
    }

    public function getAttachmentType(): ?AttachmentType
    {
        return ArrayHelper::getValue($this, 'regulation.attachmentType');
    }

    public function getName(): string
    {
        $regulation_name = ArrayHelper::getValue($this, 'regulation.name');
        $regulation_attachment_type_name = ArrayHelper::getValue($this, 'regulation.attachmentType.name');
        return "{$regulation_name} ({$regulation_attachment_type_name})";
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public function getAttachmentConnectors(): array
    {
        return [
            'owner_id' => $this->owner_id,
            'questionary_id' => $this->abiturient_questionary_id,
            'application_id' => $this->application_id,
        ];
    }

    public function getUserInstance(): User
    {
        return $this->owner ?: new User();
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }
}
