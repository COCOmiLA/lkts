<?php

namespace common\modules\abiturient\models\drafts;

use Closure;
use common\models\EmptyCheck;
use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\models\interfaces\FileToSendInterface;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\relation_presenters\BaseRelationPresenter;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ModelWithChangeHistoryHandlerInterface;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\interfaces\IHaveCallbackAfterDraftCopy;
use common\modules\abiturient\models\NeedBlockAndUpdateProcessor;
use Throwable;
use Yii;
use yii\base\Model;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class DraftsManager
{
    const REASON_RETURN = 'return_docs';
    const REASON_SENT = 'sent';
    const REASON_DECLINED = 'declined';
    const REASON_REJECTED_BY_1C = 'master_system_error';
    const REASON_APPROVED = 'approved';
    const REASON_ACTUAL_UPDATED_FROM_1C = 'actual_app_updated';
    const REASON_UPDATED_FROM_1C = 'updated';
    const REASON_MASS_REMOVAL_ADMINISTRATOR = 'mass_removal_administrator';

    const ARCHIVE_REASONS = [
        DraftsManager::REASON_RETURN => 'Подан отзыв документов',
        DraftsManager::REASON_SENT => 'Подано на проверку',
        DraftsManager::REASON_DECLINED => 'Отклонено модератором',
        DraftsManager::REASON_REJECTED_BY_1C => 'Отклонено 1С',
        DraftsManager::REASON_APPROVED => 'Принято',
        DraftsManager::REASON_ACTUAL_UPDATED_FROM_1C => 'Чистовик обновлён из 1С',
        DraftsManager::REASON_UPDATED_FROM_1C => 'Обновлено из 1С',
        DraftsManager::REASON_MASS_REMOVAL_ADMINISTRATOR => 'Массовое удаление администратором',
    ];
    public static $attributes_to_ignore = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function makeCopy(
        ActiveRecord $from,
        ActiveRecord $to = null,
        bool         $wrap_in_transaction = true,
        Closure      $additional_model_attributes_provider = null
    ): ActiveRecord
    {
        $class = get_class($from);
        if (!$to) {
            $to = new $class();
        }
        $processed_attributes = DraftsManager::excludeIgnoredProps($from);

        $transaction = null;
        if ($wrap_in_transaction) {
            $transaction = Yii::$app->db->beginTransaction();
        }
        try {
            $additional_model_attributes = [];
            if ($additional_model_attributes_provider) {
                $additional_model_attributes = $additional_model_attributes_provider($to);
            }
            $processed_attributes = ArrayHelper::merge($processed_attributes, $additional_model_attributes);
            
            DraftsManager::setModelAttributes($to, $processed_attributes);
            if ($to instanceof IHaveCallbackAfterDraftCopy && $from instanceof FileToSendInterface) {
                $to->afterDraftCopy($from);
            }
            if ($from instanceof IHasRelations) {
                foreach ($from->getRelationsInfo() as $relationInfo) {
                    
                    $attrs = $relationInfo->copyRelatedRecords($to);
                    $processed_attributes = ArrayHelper::merge($processed_attributes, $attrs);
                    
                    DraftsManager::setModelAttributes($to, $processed_attributes);
                }
            }
        } catch (\Throwable $e) {
            if ($wrap_in_transaction) {
                $transaction->rollBack();
            }
            Yii::error("Ошибка клонирования {$class}: {$e->getMessage()}", 'cloning');
            throw $e;
        }
        if ($wrap_in_transaction) {
            $transaction->commit();
        }
        return $to;
    }

    public static function SuspendHistory($model)
    {
        if ($model instanceof ModelWithChangeHistoryHandlerInterface) {
            $handler = $model->getChangeHistoryHandler();
            if ($handler) {
                $handler->setDisabled(true);
            }
        }
    }

    public static function excludeIgnoredProps(Model $model_with_attributes)
    {
        $props = $model_with_attributes->attributes;
        $ignored_attributes = DraftsManager::$attributes_to_ignore;
        if ($model_with_attributes instanceof IHaveIgnoredOnCopyingAttributes) {
            $ignored_attributes = $model_with_attributes->getIgnoredOnCopyingAttributes();
        }
        foreach ($ignored_attributes as $attr) {
            if (array_key_exists($attr, $props)) {
                unset($props[$attr]);
            }
        }
        return $props;
    }

    public static function setModelAttributes(ActiveRecord $model, array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($model->hasAttribute($key)) {
                $model->{$key} = $value;
            }
        }
        DraftsManager::SuspendHistory($model);

        $model
            ->loadDefaultValues()
            ->save(false);
    }

    public static function ensurePersisted(ActiveRecord $model): ActiveRecord
    {
        if ($model->getIsNewRecord()) {
            DraftsManager::SuspendHistory($model);

            $model
                ->loadDefaultValues()
                ->save(false);
        }
        return $model;
    }

    public static function getOrCreateApplicationDraftByOtherDraft(BachelorApplication $from, int $draft_status): BachelorApplication
    {
        if ($from->draft_status == $draft_status) {
            return $from;
        }
        $draft = DraftsManager::getApplicationDraftByOtherDraft($from, $draft_status);
        if ($draft && $draft->status != $from->status) {
            $draft
                ->setArchiveInitiator(Yii::$app->user->identity)
                ->setArchiveReason(DraftsManager::REASON_DECLINED)
                ->archive();
            $draft = null;
        }
        if (!$draft) {
            $draft = DraftsManager::createApplicationDraftByOtherDraft($from, $draft_status);
        }
        return $draft;
    }

    public static function getApplicationDraftByOtherDraft(BachelorApplication $application, int $draft_status): ?BachelorApplication
    {
        if (!$application->user) {
            return null;
        }
        if ($application->draft_status == $draft_status) {
            return $application;
        }
        return DraftsManager::getApplicationDraft($application->user, $application->type, $draft_status);
    }

    public static function createApplicationDraftByOtherDraft(BachelorApplication $from, int $to_draft_status, int $status = null): BachelorApplication
    {
        $from_draft_status = $from->draft_status;
        $transaction = Yii::$app->db->beginTransaction();
        $new_app = null;
        try {
            
            $new_app = DraftsManager::makeCopy($from);
            $new_app->draft_status = $to_draft_status;

            
            if (is_null($status)) {
                $new_app = DraftsManager::setupApplicationStatus($new_app);
            } else {
                $new_app->status = $status;
            }
            $new_app->synced_with_1C_at = ($from->draft_status == IDraftable::DRAFT_STATUS_APPROVED ? max($from->approved_at, $from->sent_at, $from->synced_with_1C_at) : $from->synced_with_1C_at);
            $new_app
                ->loadDefaultValues()
                ->setParentDraft($from)
                ->save(false);

            $new_app = ApplicationAndQuestionaryLinker::setUpQuestionaryLink($new_app);

            if ($to_draft_status != $from_draft_status && !$from->isArchive()) {
                
                $from_questionary = $from->getAbiturientQuestionary()->one();
                if ($to_draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
                    ApplicationAndQuestionaryLinker::copyQuestionaryToActual($from_questionary);
                } elseif ($from_draft_status == IDraftable::DRAFT_STATUS_APPROVED && $to_draft_status == IDraftable::DRAFT_STATUS_CREATED) {
                    ApplicationAndQuestionaryLinker::copyQuestionaryToDraft($from_questionary);
                }
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $new_app;
    }

    public static function getApplicationDraftQuery(User $user, ApplicationType $applicationType, int $draft_status): ActiveQuery
    {
        $tn = BachelorApplication::tableName();
        return BachelorApplication::find()
            ->active()
            ->andWhere([
                "{$tn}.user_id" => $user->id,
                "{$tn}.type_id" => $applicationType->id,
            ])
            ->andWhere([
                "{$tn}.draft_status" => $draft_status,
            ])
            ->orderBy(["{$tn}.updated_at" => SORT_DESC]);
    }

    public static function getApplicationDraft(User $user, ApplicationType $applicationType, int $draft_status): ?BachelorApplication
    {
        return DraftsManager::getApplicationDraftQuery($user, $applicationType, $draft_status)
            ->limit(1)
            ->one();
    }

    







    public static function createArchivePoint(BachelorApplication $entity, string $reason, int $draft_status, $initiator = null): BachelorApplication
    {
        if (!$initiator) {
            $initiator = Yii::$app->user->identity;
        }
        
        $new_entity = DraftsManager::createApplicationDraftByOtherDraft($entity, $draft_status, $entity->status);
        $entity
            ->setArchiveInitiator($initiator)
            ->setArchiveReason($reason)
            ->archive();

        return $new_entity;
    }

    public static function getOrCreateApplicationDraft(User $user, ApplicationType $applicationType, int $draft_status): array
    {
        $created = false;
        $app = DraftsManager::getApplicationDraft($user, $applicationType, $draft_status);

        if (!$app) {
            $created = true;
            $app = new BachelorApplication();
            $app->user_id = $user->id;
            $app->type_id = $applicationType->id;
            $app->draft_status = $draft_status;

            $app = DraftsManager::setupApplicationStatus($app);

            $app
                ->loadDefaultValues()
                ->save(false);

            ApplicationAndQuestionaryLinker::setUpQuestionaryLink($app);
        }
        return [$app, $created];
    }

    private static function setupApplicationStatus(BachelorApplication $app)
    {
        $draft_status = $app->draft_status;
        if ($draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            $app->status = ApplicationInterface::STATUS_APPROVED;
        } elseif ($draft_status == IDraftable::DRAFT_STATUS_CREATED) {
            $app->status = ApplicationInterface::STATUS_CREATED;
        } elseif (!$app->moderationAllowedByStatus()) { 
            $app->status = ApplicationInterface::STATUS_SENT;
        }
        return $app;
    }

    public static function getActualApplication(User $user, ApplicationType $type, bool $forced = false): ?BachelorApplication
    {
        $needs_update = false;
        $isIn1C = $user->hasAppInOneS($type);
        
        $application = DraftsManager::getApplicationDraft($user, $type, IDraftable::DRAFT_STATUS_APPROVED);
        if ($isIn1C && $application && !$forced) {
            [$needs_update, $_] = NeedBlockAndUpdateProcessor::getProcessedNeedBlockAndUpdate($application);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$isIn1C && $application) {
                $application->delete();
                $application = null;
            }
            if ($isIn1C) {
                if (!$application) {
                    
                    [$application, $_] = DraftsManager::getOrCreateApplicationDraft($user, $type, IDraftable::DRAFT_STATUS_APPROVED);
                    $forced = true;
                }

                if ($forced || $needs_update) {
                    $application->fullUpdateFrom1C();
                    if ($application->type->archive_actual_app_on_update) {
                        
                        $application = DraftsManager::createArchivePoint(
                            $application,
                            DraftsManager::REASON_ACTUAL_UPDATED_FROM_1C,
                            IDraftable::DRAFT_STATUS_APPROVED
                        );
                    }
                }
            }
            if ($application) {
                $application->status = ApplicationInterface::STATUS_APPROVED;
                if (EmptyCheck::isEmpty($application->approver_id)) {
                    $application->approver_id = 1;
                }
                $application
                    ->loadDefaultValues()
                    ->save(false);
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $application;
    }

    public static function getActualQuestionary(User $user, bool $update = false): ?AbiturientQuestionary
    {
        if (!$user->userRef) {
            return null;
        }

        $questionary = $user->getActualAbiturientQuestionary()->one();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$questionary) {
                $editable_questionary = $user->getAbiturientQuestionary()->one();
                if ($editable_questionary) {
                    $questionary = DraftsManager::makeCopy($editable_questionary);
                } else {
                    $questionary = new AbiturientQuestionary();
                    $questionary->user_id = $user->id;
                    $questionary->loadDefaultValues();
                }
                $questionary->status = AbiturientQuestionary::STATUS_CREATE_FROM_1C;
                $questionary->draft_status = IDraftable::DRAFT_STATUS_APPROVED;
                $update = true;
            }
            if ($update) {
                if (!$questionary->getFrom1CWithParents()) {
                    throw new UserException('Не удалось получить данные из Информационной системы вуза');
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage(), 'ActualUpdate');
            Yii::$app->session->setFlash('alert', [
                'body' => $e->getMessage(),
                'options' => ['class' => 'alert-danger']
            ]);
            return null;
        }
        return $questionary;
    }

    public static function clearOldModerations(BachelorApplication $app, IEntrantManager $initiator, string $reason)
    {
        if ($app->isArchive()) {
            throw new UserException("Вы работаете с устаревшей версией заявления");
        }
        $old_moderating_apps = BachelorApplication::find()
            ->active()
            ->andWhere(['draft_status' => IDraftable::DRAFT_STATUS_MODERATING])
            ->andWhere(['not', ['id' => $app->id]])
            ->andWhere([
                'user_id' => $app->user->id,
                'type_id' => $app->type->id,
            ])
            ->all();
        
        foreach ($old_moderating_apps as $old_moderating_app) {
            $old_moderating_app
                ->setArchiveInitiator($initiator)
                ->setArchiveReason($reason)
                ->archive();
        }
    }

    public static function clearOldSendings(BachelorApplication $app, IEntrantManager $initiator, string $reason)
    {
        if ($app->isArchive()) {
            throw new UserException("Вы работаете с устаревшей версией заявления");
        }
        $old_apps = BachelorApplication::find()
            ->active()
            ->andWhere(['draft_status' => IDraftable::DRAFT_STATUS_SENT])
            ->andWhere([
                'user_id' => $app->user->id,
                'type_id' => $app->type->id,
            ])
            ->andWhere(['not', ['id' => $app->id]])
            ->all();
        
        foreach ($old_apps as $old_app) {
            $old_app
                ->setArchiveInitiator($initiator)
                ->setArchiveReason($reason)
                ->archive();
        }
    }

    









    public static function removeOldApproved(BachelorApplication $app, IEntrantManager $initiator, string $reason)
    {
        if ($app->isArchive()) {
            throw new UserException("Вы работаете с устаревшей версией заявления");
        }
        $provide_real_deletion = !$app->type->archive_actual_app_on_update;

        
        $old_apps = BachelorApplication::find()
            ->active()
            ->andWhere(['draft_status' => IDraftable::DRAFT_STATUS_APPROVED])
            ->andWhere([
                'user_id' => $app->user->id,
                'type_id' => $app->type->id,
            ])
            ->andWhere(['not', ['id' => $app->id]])
            ->all();

        foreach ($old_apps as $old_app) {
            if ($provide_real_deletion) {
                $old_app->delete();
            } else {
                $old_app
                    ->setArchiveInitiator($initiator)
                    ->setArchiveReason($reason)
                    ->archive();
            }
        }
    }
}
