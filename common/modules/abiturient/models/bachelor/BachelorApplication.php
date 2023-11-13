<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\ManagerNotificationsConfigurator;
use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\ApplicationSendHandler\FullPacketSendHandler\FullPacketSendHandler;
use common\components\ApplicationSendHandler\interfaces\IApplicationSendHandler;
use common\components\ApplicationSendHandler\LocalDataUpdaters\FullPackageUpdateHandler;
use common\components\ApplicationSendHandler\NonSandboxSendHandler\NonSandboxSendHandler;
use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\applyingSteps\StepsFactories\BaseStepsFactory;
use common\components\applyingSteps\StepsFactories\FullPackageStepFactory;
use common\components\ArchiveAdmissionCampaignHandler;
use common\components\AttachmentManager;
use common\components\CommentNavigationLinkerWidget\CommentNavigationLinkerWidget;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\components\IdentityManager\IdentityManager;
use common\components\queries\ArchiveQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\RegulationRelationManager;
use common\components\soapException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\DocumentType;
use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\EmptyCheck;
use common\models\EntrantManager;
use common\models\errors\RecordNotValid;
use common\models\interfaces\CanUseMultiplyEducationDataInterface;
use common\models\interfaces\IArchiveWithInitiator;
use common\models\interfaces\ILinkedToParentDraft;
use common\models\MasterSystemManager;
use common\models\relation_presenters\AttachmentsRelationPresenter;
use common\models\relation_presenters\ManyToManyRelationPresenter;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\repositories\UserRegulationRepository;
use common\models\ToAssocCaster;
use common\models\traits\ArchiveTrait;
use common\models\traits\BachelorApplicationAdmissionAgreementTrait;
use common\models\traits\CheckAbiturientAccessibilityTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\AllAgreements\AgreementRecord;
use common\modules\abiturient\models\bachelor\AllAgreements\AllAgreementsHandler;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClassInput;
use common\modules\abiturient\models\bachelor\extensions\BachelorApplicationFileAttacher;
use common\modules\abiturient\models\CommentsComing;
use common\modules\abiturient\models\drafts\ApplicationAndQuestionaryLinker;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use common\modules\abiturient\models\NeedBlockAndUpdateProcessor;
use common\modules\abiturient\models\repositories\FileRepository;
use common\modules\abiturient\traits\bachelor\BachelorApplicationAutofillSpecialityTrait;
use common\modules\abiturient\traits\bachelor\BachelorApplicationDefaultSpecialityParamTrait;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;














































































class BachelorApplication extends ActiveRecord implements
    ApplicationInterface,
    IHasRelations,
    IArchiveWithInitiator,
    ILinkedToParentDraft,
    IDraftable,
    ICanAttachFile,
    CanUseMultiplyEducationDataInterface
{
    use ArchiveTrait;
    use BachelorApplicationAdmissionAgreementTrait;
    use BachelorApplicationAutofillSpecialityTrait;
    use BachelorApplicationDefaultSpecialityParamTrait;
    use CheckAbiturientAccessibilityTrait;
    use HtmlPropsEncoder;

    public const SCENARIO_APPLICATION_WITH_EDUCATION = 'application_with_education';

    
    public $applyingSteps;

    public ArchiveAdmissionCampaignHandler $archiveAdmissionCampaignHandler;
    private BachelorApplicationFileAttacher $applicationFileAttacher;

    private IApplicationSendHandler $sandboxHandler;
    private IApplicationSendHandler $nonSandboxHandler;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->archiveAdmissionCampaignHandler = new ArchiveAdmissionCampaignHandler($this);
        $this->applicationFileAttacher = new BachelorApplicationFileAttacher($this);

        $this->initApplyingSteps(new FullPackageStepFactory());
        $this->sandboxHandler = new FullPacketSendHandler($this);

        $this->nonSandboxHandler = new NonSandboxSendHandler($this);
    }

    protected function initApplyingSteps(BaseStepsFactory $factory)
    {
        $this->applyingSteps = $factory->getSteps($this);
    }

    public function getAgreementRecords()
    {
        return $this->hasMany(AgreementRecord::class, ['application_id' => 'id']);
    }

    public function getAgreementRecordsWithoutActiveSpeciality()
    {
        return $this->getAgreementRecords()
            ->joinWith(['speciality speciality'])
            ->andWhere(['speciality.id' => null])
            ->orderBy([AgreementRecord::tableName() . '.date' => SORT_ASC]);
    }

    public function getAgreementRecordsWithActiveSpeciality()
    {
        return $this->getAgreementRecords()
            ->joinWith(['speciality speciality'])
            ->andWhere(['not', ['speciality.id' => null]])
            ->orderBy([AgreementRecord::tableName() . '.date' => SORT_ASC]);
    }

    public static function tableName()
    {
        return '{{%bachelor_application}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'status',
                    'approver_id',
                    'sent_at',
                    'approved_at',
                    'synced_with_1C_at',
                    'block_status',
                    'type_id',
                    'last_manager_id',
                    'last_management_at',
                    'archived_at',
                    'draft_status',
                    'parent_draft_id',
                    'archived_by_user_id',
                ],
                'integer'
            ],
            [
                [
                    'archive',
                    'have_order',
                    'need_exams',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'have_order',
                    'need_exams',
                ],
                'default',
                'value' => false
            ],
            [
                ['moderator_comment', 'archive_reason'],
                'string',
                'max' => 2000
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_CREATED
            ],
            [
                'draft_status',
                'default',
                'value' => self::DRAFT_STATUS_CREATED
            ],
            [
                ['status'],
                'in',
                'range' => [
                    self::STATUS_CREATED,
                    self::STATUS_SENT,
                    self::STATUS_APPROVED,
                    self::STATUS_NOT_APPROVED,
                    self::STATUS_REJECTED_BY1C,
                    self::STATUS_WANTS_TO_BE_REMOTE,
                    self::STATUS_WANTS_TO_RETURN_ALL,
                    self::STATUS_SENT_AFTER_APPROVED,
                    self::STATUS_SENT_AFTER_NOT_APPROVED,
                    self::STATUS_ENROLLMENT_REJECTION_REQUESTED,
                ]
            ],
            [
                ['draft_status'],
                'in',
                'range' => [
                    self::DRAFT_STATUS_CREATED,
                    self::DRAFT_STATUS_SENT,
                    self::DRAFT_STATUS_MODERATING,
                    self::DRAFT_STATUS_APPROVED,
                ]
            ],
            [
                'block_status',
                'default',
                'value' => self::BLOCK_STATUS_DISABLED
            ],
            [
                ['block_status'],
                'in',
                'range' => [
                    self::BLOCK_STATUS_DISABLED,
                    self::BLOCK_STATUS_ENABLED
                ]
            ],
            [
                'moderator_comment',
                'required',
                'when' => function ($model) {
                    return ($model->status == self::STATUS_NOT_APPROVED && Yii::$app->configurationManager->sandboxEnabled);
                },
                
                
                'whenClient' => "function (attribute, value) {
                    var action = $('#questionary-form').attr('action');
                    return action && action.startsWith('/sandbox/decline');
            }"
            ],
            [
                ['blocker_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['blocker_id' => 'id']
            ],
            [
                ['entrant_manager_blocker_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => EntrantManager::class,
                'targetAttribute' => ['entrant_manager_blocker_id' => 'id']
            ],
            [
                ['educationsDataTagList'],
                'required',
                'on' => [self::SCENARIO_APPLICATION_WITH_EDUCATION],
                'when' => function ($model) {
                    return !$model->isNewRecord && $model->type->rawCampaign->common_education_document;
                }
            ],
            [
                ['educationsDataTagList'],
                'safe',
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_APPLICATION_WITH_EDUCATION] = array_merge($scenarios['default'], ['educationsDataTagList']);

        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'fio' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "fio"; формы "Заявления": `ФИО`'),
            'status' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "status"; формы "Заявления": `Статус`'),
            'sent_at' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "sent_at"; формы "Заявления": `Дата подачи`'),
            'archive' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "archive"; формы "Заявления": `Статус активности`'),
            'user_id' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "user_id"; формы "Заявления": `Анкета`'),
            'usermail' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "usermail"; формы "Заявления": `Email`'),
            'created_at' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "created_at"; формы "Заявления": `Дата создания`'),
            'approved_at' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "approved_at"; формы "Заявления": `Проверено в`'),
            'approver_id' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "approver_id"; формы "Заявления": `Проверивший модератор`'),
            'archived_at' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "archive_at"; формы "Заявления": `Дата архивации`'),
            'block_status' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "block_status"; формы "Заявления": `Блокировка редактирования`'),
            'draft_status' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "draft_status" формы "Заявления": `Статус черновика`'),
            'archive_reason' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "archive_reason"; формы "Заявления": `Причина архивации`'),
            'lastManagerName' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "lastManagerName"; формы "Заявления": `Имя модератора`'),
            'last_manager_id' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "last_manager_id"; формы "Заявления": `Имя модератора`'),
            'moderator_comment' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "moderator_comment"; формы "Заявления": `Комментарий`'),
            'last_management_at' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "last_management_at"; формы "Заявления": `Дата обработки`'),
            'archiveInitiatorName' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "archiveInitiatorName"; формы "Заявления": `Инициатор архивации`'),
            'educationsDataTagList' => Yii::t('abiturient/bachelor/bachelor-application', 'Подпись для поля "educationsDataTagList" формы "НП": `Данные об образовании`'),
        ];
    }

    public function getCommentsComing()
    {
        return $this->hasMany(CommentsComing::class, ['bachelor_application_id' => 'id']);
    }

    public function getApplicantsComment()
    {
        $commentsComing = $this->getCommentsComing()
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        return $commentsComing ? $commentsComing->comment : '-';
    }

    public function getLastManager()
    {
        return $this->hasOne(User::class, ['id' => 'last_manager_id']);
    }

    public function getLastManagerName()
    {
        $lastManager = $this->lastManager;
        if (isset($lastManager)) {
            $lastManagerName = $lastManager->username;
            if ($lastManagerName) {
                return $lastManagerName;
            }
            return Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Текст сообщения когда заявления некто не проверял; формы "Заявления": `Не проверялось`'
            );
        }
        return '-';
    }

    






    public function setLastManager($id = null): void
    {
        if (isset($id)) {
            $this->last_manager_id = $id;
            $this->last_management_at = time();

            $sentApp = null;
            if ($this->draft_status == IDraftable::DRAFT_STATUS_MODERATING) {
                $sentApp = DraftsManager::getApplicationDraftByOtherDraft($this, IDraftable::DRAFT_STATUS_SENT);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$this->save(false, ['last_manager_id', 'last_management_at'])) {
                    throw new RecordNotValid($this);
                }

                if ($sentApp) {
                    $sentApp->last_manager_id = $this->last_manager_id;
                    $sentApp->last_management_at = $this->last_management_at;

                    if (!$sentApp->save(false, ['last_manager_id', 'last_management_at'])) {
                        throw new RecordNotValid($sentApp);
                    }
                }

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();

                Yii::error("Ошибка установки «Последнего модератора», по причине: {$e->getMessage()}", 'BachelorApplication.setLastManager');

                throw $e;
            }
        }
    }

    


    public function getEducationsDataTagList(): ?array
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnJunction = BachelorSpeciality::getBachelorSpecialityEducationDataTable();

        $educationsData = $this->getSpecialitiesWithoutOrdering()
            ->select(["DISTINCT {$tnJunction}.education_data_id"])
            ->innerJoin($tnJunction, "{$tnBachelorSpeciality}.id = {$tnJunction}.bachelor_speciality_id")
            ->column();
        if ($educationsData) {
            return $educationsData;
        }

        return null;
    }

    




    public function setEducationsDataTagList($educationsDataTagList): void
    {
        if (!is_array($educationsDataTagList)) {
            $educationsDataTagList = [$educationsDataTagList];
        }
        $educationsDataTagList = array_merge(
            $educationsDataTagList,
            $this->getEducationsDataFromEnlistedSpeciality()
        );

        $specialities = $this->specialities;
        foreach ($specialities as $specialty) {
            $specialty->educationsDataTagList = $educationsDataTagList;
        }
    }

    


    private function getEducationsDataFromEnlistedSpeciality(): array
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnJunction = BachelorSpeciality::getBachelorSpecialityEducationDataTable();

        return $this->hasEnlistedBachelorSpecialityQuery()
            ->select(["DISTINCT {$tnJunction}.education_data_id"])
            ->innerJoin($tnJunction, "{$tnBachelorSpeciality}.id = {$tnJunction}.bachelor_speciality_id")
            ->column();
    }

    


    public function getUser()
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getType()
    {
        return $this->hasOne(ApplicationType::class, ['id' => 'type_id']);
    }

    


    public function getEgeResults($year = null)
    {
        $tnEgeResult = EgeResult::tableName();
        $actualEntrantTestList = $this
            ->getAllEgeResults()
            ->active();
        if ($year != null) {
            $actualEntrantTestList->andWhere(["{$tnEgeResult}.egeyear" => $year]);
        }
        return $actualEntrantTestList;
    }

    






    public function getAllEgeResults()
    {
        return $this->hasMany(EgeResult::class, ['application_id' => 'id']);
    }

    




    public function getRawSpecialities()
    {
        return $this->hasMany(BachelorSpeciality::class, ['application_id' => 'id']);
    }

    public function getRawSpecialitiesWithEagerLoad()
    {
        return $this->getRawSpecialities()
            ->with([
                'admissionCategory',
                'speciality',
                'speciality.campaignRef',
                'speciality.competitiveGroupRef',
                'speciality.curriculumRef',
                'speciality.educationLevelRef',
                'speciality.subdivisionRef',
                'speciality.educationSourceRef',
            ]);
    }

    


    public function getSpecialities()
    {
        return $this->getSpecialitiesWithoutOrdering()
            ->joinWith(['specialityPriority speciality_priority'])
            ->orderBy(["speciality_priority.enrollment_priority" => SORT_ASC, 'speciality_priority.inner_priority' => SORT_ASC]);
    }

    


    public function getSpecialitiesWithoutOrdering()
    {
        return $this->getAllBachelorSpecialities()
            ->active();
    }

    public function getBachelorPreferencesOlympForBVI()
    {
        return $this->getBachelorPreferencesOlymp()
            ->joinWith(['olympiad.olympiadFilters'])
            ->andWhere([OlympiadFilter::tableName() . '.variant_of_retest_ref_id' => Yii::$app->configurationManager->getCode('without_entrant_tests_variant')]);
    }

    




    public function getAllBachelorSpecialities()
    {
        return $this->getRawSpecialities()
            ->with(['speciality']);
    }

    


    public function getEducations()
    {
        return $this->getRawEducations()->active();
    }

    


    public function getRawEducations()
    {
        return $this->hasMany(EducationData::class, ['application_id' => 'id']);
    }

    public function getAllAttachments()
    {
        $sort = Yii::$app->configurationManager->getCode('scan_sort_code');
        $orderBy = null;
        if ($sort !== null) {
            switch ($sort) {
                case '1':
                    $orderBy = [File::tableName() . '.upload_name' => SORT_ASC];
                    break;

                case '2':
                    $orderBy = ['id' => SORT_ASC];
                    break;

                case '0':
                    break;
            }
        }
        $query = $this->hasMany(Attachment::class, ['application_id' => 'id'])
            ->joinWith('attachmentType')
            ->joinWith('linkedFile')
            ->andOnCondition([Attachment::tableName() . '.deleted' => false]);
        if ($orderBy) {
            $query->orderBy($orderBy);
        }
        return $query;
    }

    public function getEntireApplicationAttachmentCollections(): array
    {
        $collections = [];
        $collections = array_merge($collections, FileRepository::GetAttachmentCollectionsFromTypes($this, RegulationRelationManager::GetFullRelatedListForApplication()));
        $regulations = UserRegulationRepository::GetUserRegulationsWithFilesByApplicationAndRelatedEntity($this, RegulationRelationManager::GetFullRelatedListForApplication());

        $collections = array_merge($collections, ArrayHelper::getColumn($regulations, 'attachmentCollection'));
        $collections = array_merge($collections, ArrayHelper::getColumn($this->educations, 'attachmentCollection'));
        $collections = array_merge($collections, [$this->getApplicationReturnAttachmentCollection()]);

        $preferences = [];
        $preferences = array_merge($preferences, $this->preferences ?? []);
        $preferences = array_merge($preferences, $this->bachelorTargetReceptions ?? []);
        $collections = array_merge($collections, ArrayHelper::getColumn($preferences, 'attachmentCollection'));

        
        return array_merge($collections, ArrayHelper::getColumn($this->individualAchievements, 'attachmentCollection'));
    }

    public function getAllAttachmentsWithoutRegulations()
    {
        return $this->getAllAttachments()
            ->leftJoin('regulation r', 'r.attachment_type = attachment.attachment_type_id')
            ->andOnCondition(['r.id' => null]);
    }

    public function getEgeAttachments()
    {
        return $this->getAllAttachmentsWithoutRegulations()
            ->andOnCondition(['attachment_type.related_entity' => AttachmentType::RELATED_ENTITY_EGE]);
    }

    public function getEduAttachments()
    {
        return $this->getAllAttachmentsWithoutRegulations()
            ->andOnCondition(['attachment_type.related_entity' => AttachmentType::RELATED_ENTITY_EDUCATION]);
    }

    public function getAttachments()
    {
        return $this->getAllAttachmentsWithoutRegulations()
            ->andOnCondition(['attachment_type.related_entity' => AttachmentType::RELATED_ENTITY_APPLICATION]);
    }

    public function getBenefitsAttachments()
    {
        return $this->getAllAttachmentsWithoutRegulations()
            ->andOnCondition(['attachment_type.related_entity' => [
                AttachmentType::RELATED_ENTITY_OLYMPIAD,
                AttachmentType::RELATED_ENTITY_PREFERENCE,
                AttachmentType::RELATED_ENTITY_TARGET_RECEPTION
            ]]);
    }

    public function getHistory()
    {
        return $this->hasMany(ApplicationHistory::class, ['application_id' => 'id']);
    }

    public function getModerateHistory()
    {
        return $this->hasMany(ModerateHistory::class, ['application_id' => 'id']);
    }

    public function getHistoryString()
    {
        $str = '';
        foreach ($this->history as $history) {
            $str .= $history->typeName . ', ';
        }
        return rtrim(trim((string)$str), ',');
    }

    















    public function getModerateApprovedAt(): ?int
    {
        $moderatorial = $this->getLastModerateHistory(BachelorApplication::STATUS_APPROVED);
        if (is_null($moderatorial)) {
            return null;
        }

        return $moderatorial->updated_at;
    }

    






    public function getLastModerateHistory($status = null)
    {
        return ModerateHistory::find()
            ->where(['application_id' => $this->id])
            ->andFilterWhere(['status' => $status])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function campaignAllowedToSend(): bool
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $readonly_spec = BachelorSpeciality::find()
            ->joinWith('application')
            ->active()
            ->andWhere([
                "{$tnBachelorSpeciality}.readonly" => true,
                "{$tnBachelorSpeciality}.application_id" => $this->id,
            ])
            ->andWhere(['NOT IN', "{$tnBachelorApplication}.draft_status", BachelorApplication::DRAFT_STATUS_CREATED])
            ->exists();
        if ($readonly_spec) {
            return false;
        }
        if ($this->type->blocked) {
            return false;
        }
        return true;
    }

    private function hasEnlistedBachelorSpecialityQuery(): ActiveQuery
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        return BachelorSpeciality::find()
            ->joinWith('application')
            ->active()
            ->andWhere([
                "{$tnBachelorSpeciality}.is_enlisted" => true,
                "{$tnBachelorSpeciality}.application_id" => $this->id,
            ])
            ->andWhere(['IN', "{$tnBachelorApplication}.draft_status", BachelorApplication::DRAFT_STATUS_CREATED]);
    }

    public function hasEnlistedBachelorSpeciality(): bool
    {
        return $this->hasEnlistedBachelorSpecialityQuery()->exists();
    }

    


    public function canEdit()
    {
        if (!$this->campaignAllowedToSend()) {
            return false;
        }
        if ($this->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            return false;
        }

        if (!$this->hasEnlistedBachelorSpeciality()) {
            
            $approved_app = DraftsManager::getApplicationDraftByOtherDraft($this, IDraftable::DRAFT_STATUS_APPROVED);
            if ($approved_app && $approved_app->have_order == true) {
                return false;
            }
        }

        $current_user = Yii::$app->user->identity;
        if ($current_user) {
            if ($current_user->isModer()) {
                if ($this->type->moderator_allowed_to_edit) {
                    return $this->draft_status == $this->getDraftStatusToModerate();
                }
            } else {
                if ($current_user->id == $this->user->id) {
                    return $this->draft_status == IDraftable::DRAFT_STATUS_CREATED;
                }
            }
        }
        return false;
    }

    public function getDraftStatusToModerate(): int
    {
        if ($this->type->persist_moderators_changes_in_sent_application) {
            return IDraftable::DRAFT_STATUS_SENT;
        }
        return IDraftable::DRAFT_STATUS_MODERATING;
    }

    public function getModeratingApplication(): ?BachelorApplication
    {
        if ($this->draft_status == IDraftable::DRAFT_STATUS_MODERATING) {
            return $this;
        }
        return DraftsManager::getApplicationDraftByOtherDraft($this, $this->getDraftStatusToModerate());
    }

    public function canBeSentToModerate(): bool
    {
        if ($this->draft_status == IDraftable::DRAFT_STATUS_CREATED && $this->campaignAllowedToSend()) {
            $moderating_draft = $this->getModeratingApplication();
            if (!$moderating_draft) {
                return true;
            }
            [$blocked, $_] = $moderating_draft->isApplicationBlocked();
            return !$blocked;
        }
        return false;
    }

    public function canEditSpecialities()
    {
        return $this->type->haveStageOne() && $this->type->haveStageTwo();
    }

    public function translateArchiveStatus(): string
    {
        if ($this->archive) {
            return Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Название статуса активности; формы "Заявления": `В архиве`'
            );
        }
        return Yii::t(
            'abiturient/bachelor/bachelor-application',
            'Название статуса активности; формы "Заявления": `Активно`'
        );
    }

    public static function rawTranslateStatus(?int $status)
    {
        if (!is_null($status)) {
            switch ($status) {
                case BachelorApplication::STATUS_CREATED:
                    return Yii::$app->configurationManager->getText('status_created');

                case BachelorApplication::STATUS_SENT:
                    return Yii::$app->configurationManager->getText('status_sent');

                case BachelorApplication::STATUS_APPROVED:
                    return Yii::$app->configurationManager->getText('status_approved');

                case BachelorApplication::STATUS_NOT_APPROVED:
                    return Yii::$app->configurationManager->getText('status_not_approved');

                case BachelorApplication::STATUS_REJECTED_BY1C:
                    return Yii::$app->configurationManager->getText('status_rejected_by1_c');

                case BachelorApplication::STATUS_SENT_AFTER_APPROVED:
                    return Yii::$app->configurationManager->getText('status_sent_after_approved');

                case BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED:
                    return Yii::$app->configurationManager->getText('status_sent_after_not_approved');

                case BachelorApplication::STATUS_WANTS_TO_RETURN_ALL:
                    return Yii::$app->configurationManager->getText('status_wants_to_return_all');

                case BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED:
                    return Yii::$app->configurationManager->getText('status_enrollment_rejection_requested');
            }
        }
        return '';
    }

    public function translateStatus()
    {
        if ($this->moderatingNow()) {
            return Yii::$app->configurationManager->getText('draft_status_application_moderating');
        }
        return BachelorApplication::rawTranslateStatus($this->status);
    }

    public function translateDraftStatus(): string
    {
        return BachelorApplication::rawTranslateDraftStatus($this->draft_status);
    }

    public static function rawTranslateDraftStatus(?int $status): string
    {
        $status_name = '';
        switch ($status) {
            case IDraftable::DRAFT_STATUS_CREATED:
                $status_name = Yii::$app->configurationManager->getText('draft_status_application_preparing');
                break;
            case IDraftable::DRAFT_STATUS_SENT:
                $status_name = Yii::$app->configurationManager->getText('draft_status_application_sent');
                break;
            case IDraftable::DRAFT_STATUS_MODERATING:
                $status_name = Yii::$app->configurationManager->getText('draft_status_application_moderating');
                break;
            case IDraftable::DRAFT_STATUS_APPROVED:
                $status_name = Yii::$app->configurationManager->getText('draft_status_application_clean_copy');
                break;
            default:
                throw new UserException("Не известный статус черновика");
        }
        return $status_name;
    }

    public function getTooltipNameByStatus()
    {
        if ($this->moderatingNow()) {
            return 'moderating_now_app_status_tooltip';
        }
        switch ($this->status) {
            case BachelorApplication::STATUS_CREATED:
                return 'created_app_status_tooltip';

            case BachelorApplication::STATUS_SENT:
                return 'sent_app_status_tooltip';

            case BachelorApplication::STATUS_APPROVED:
                return 'approved_app_status_tooltip';

            case BachelorApplication::STATUS_NOT_APPROVED:
                return 'not_approved_app_status_tooltip';

            case BachelorApplication::STATUS_REJECTED_BY1C:
                return 'rejected_by_one_s_app_status_tooltip';

            case BachelorApplication::STATUS_SENT_AFTER_APPROVED:
                return 'sent_after_approved_app_status_tooltip';

            case BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED:
                return 'sent_after_not_approved_app_status_tooltip';

            case BachelorApplication::STATUS_WANTS_TO_RETURN_ALL:
                return 'return_all_app_status_tooltip';

            case BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED:
                return 'enrollment_rejection_app_status_tooltip';
        }
        return '';
    }

    


    public function moderationAllowedByStatus()
    {
        $statuses = [
            BachelorApplication::STATUS_SENT,
            BachelorApplication::STATUS_SENT_AFTER_APPROVED,
            BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
            BachelorApplication::STATUS_REJECTED_BY1C,
            BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
            BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED,
        ];

        return in_array($this->status, $statuses);
    }

    


    public function viewerAllowedByStatus()
    {
        $statuses = [
            BachelorApplication::STATUS_SENT,
            BachelorApplication::STATUS_APPROVED,
            BachelorApplication::STATUS_NOT_APPROVED,
            BachelorApplication::STATUS_REJECTED_BY1C,
            BachelorApplication::STATUS_WANTS_TO_BE_REMOTE,
            BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
            BachelorApplication::STATUS_SENT_AFTER_APPROVED,
            BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
            BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED,
        ];

        return in_array($this->status, $statuses);
    }

    public function isNotCreatedDraft(): bool
    {
        return $this->draft_status != IDraftable::DRAFT_STATUS_CREATED;
    }

    public function isDraftInSendMode(): bool
    {
        return in_array($this->draft_status, [IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_MODERATING]);
    }

    public function isArchivedApprovedDraft(): bool
    {
        return $this->draft_status == IDraftable::DRAFT_STATUS_APPROVED && $this->isArchive();
    }

    public function hasSentToModerateRecordInHistory(): bool
    {
        return BachelorApplication::find()
            ->andWhere([
                'user_id' => $this->user->id,
                'type_id' => $this->type->id,
            ])
            ->andWhere([
                'draft_status' => IDraftable::DRAFT_STATUS_SENT,
            ])
            ->andWhere([
                'not',
                ['status' => ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL]
            ])
            ->exists();
    }

    public function hasDeclinedRecordInHistory(): bool
    {
        return BachelorApplication::find()
            ->andWhere([
                'user_id' => $this->user->id,
                'type_id' => $this->type->id,
            ])
            ->andWhere([
                'draft_status' => IDraftable::DRAFT_STATUS_SENT,
            ])
            ->andWhere(
                ['status' => [ApplicationInterface::STATUS_NOT_APPROVED, ApplicationInterface::STATUS_REJECTED_BY1C]]
            )
            ->exists();
    }

    public function hasActiveDeclinedApplication(): bool
    {
        return BachelorApplication::find()
            ->active()
            ->andWhere([
                'user_id' => $this->user->id,
                'type_id' => $this->type->id,
            ])
            ->andWhere([
                'draft_status' => IDraftable::DRAFT_STATUS_SENT,
            ])
            ->andWhere(
                ['status' => ApplicationInterface::STATUS_NOT_APPROVED]
            )
            ->exists();
    }

    public function getEgeStatusMessage()
    {
        if ($this->moderatingNow()) {
            return Yii::$app->configurationManager->getText('ege_blocked', $this->type ?? null);
        }

        switch ($this->status) {
            case ApplicationInterface::STATUS_SENT:
            case ApplicationInterface::STATUS_SENT_AFTER_APPROVED:
            case ApplicationInterface::STATUS_SENT_AFTER_NOT_APPROVED:
                return Yii::$app->configurationManager->getText('ege_sended', $this->type ?? null);
            case ApplicationInterface::STATUS_APPROVED:
                if (Yii::$app->configurationManager->sandboxEnabled) {
                    return Yii::$app->configurationManager->getText('ege_approved_sandbox_on', $this->type ?? null);
                } else {
                    return Yii::$app->configurationManager->getText('ege_approved_sandbox_off', $this->type ?? null);
                }
            case (ApplicationInterface::STATUS_NOT_APPROVED):
                return Yii::$app->configurationManager->getText('ege_notapproved', $this->type ?? null);
            case (ApplicationInterface::STATUS_REJECTED_BY1C):
                return Yii::$app->configurationManager->getText('ege_rejected_by1c', $this->type ?? null);
            default:
                return false;
        }
    }

    public function getStatusMessage()
    {
        if ($this->moderatingNow()) {
            return Yii::$app->configurationManager->getText('application_blocked', $this->type ?? null);
        }

        switch ($this->status) {
            case ApplicationInterface::STATUS_SENT:
            case ApplicationInterface::STATUS_SENT_AFTER_APPROVED:
            case ApplicationInterface::STATUS_SENT_AFTER_NOT_APPROVED:
                return Yii::$app->configurationManager->getText('application_sended', $this->type ?? null);
            case (ApplicationInterface::STATUS_APPROVED):
                if (Yii::$app->configurationManager->sandboxEnabled) {
                    return Yii::$app->configurationManager->getText('application_approved_sandbox_on', $this->type ?? null);
                } else {
                    return Yii::$app->configurationManager->getText('application_approved_sandbox_off', $this->type ?? null);
                }
            case (ApplicationInterface::STATUS_NOT_APPROVED):
                return Yii::$app->configurationManager->getText('application_notapproved', $this->type ?? null);
            case (ApplicationInterface::STATUS_REJECTED_BY1C):
                return Yii::$app->configurationManager->getText('application_rejected_by1c', $this->type ?? null);
            default:
                return false;
        }
    }

    




    public static function sandboxMessages()
    {
        return [
            static::STATUS_CREATED => Yii::$app->configurationManager->getText('status_created'),
            static::STATUS_SENT => Yii::$app->configurationManager->getText('status_sent'),
            static::STATUS_APPROVED => Yii::$app->configurationManager->getText('status_approved'),
            static::STATUS_NOT_APPROVED => Yii::$app->configurationManager->getText('status_not_approved'),
            static::STATUS_REJECTED_BY1C => Yii::$app->configurationManager->getText('status_rejected_by1_c'),
            static::STATUS_SENT_AFTER_APPROVED => Yii::$app->configurationManager->getText('status_sent_after_approved'),
            static::STATUS_SENT_AFTER_NOT_APPROVED => Yii::$app->configurationManager->getText('status_sent_after_not_approved'),
            static::STATUS_WANTS_TO_RETURN_ALL => Yii::$app->configurationManager->getText('status_wants_to_return_all'),
            static::STATUS_ENROLLMENT_REJECTION_REQUESTED => Yii::$app->configurationManager->getText('status_enrollment_rejection_requested'),
        ];
    }

    public function getSandboxMessage()
    {
        $messages = static::sandboxMessages();
        return $messages[$this->status] ?: false;
    }

    


    public function checkBlockByTimeout()
    {
        $baseCondition = ($this->block_status == self::BLOCK_STATUS_ENABLED && ($this->entrant_manager_blocker_id !== null || $this->blocker_id !== null));
        if ($baseCondition) {
            $time_to_wait = 30 * 60; 
            $updated_at = $this->updated_at;
            $blocked_time = time() - $updated_at;

            if ($blocked_time > $time_to_wait) {
                $this->unblockApplication();
                return [false, 0];
            }
            return [true, $time_to_wait - $blocked_time];
        }
        return [false, 0];
    }

    public function moderatingNow(): bool
    {
        [$blocked, $_] = $this->isApplicationBlocked();
        return $blocked;
    }

    public function fullUpdateFrom1C()
    {
        (new FullPackageUpdateHandler($this))->update();
    }

    public function getEgeYears()
    {
        $tnStoredDisciplineFormReferenceType = StoredDisciplineFormReferenceType::tableName();
        return ArrayHelper::map($this
            ->getEgeResults()
            ->joinWith('cgetExamForm')
            ->andWhere(["{$tnStoredDisciplineFormReferenceType}.reference_uid" => Yii::$app->configurationManager->getCode('discipline_ege_form')])
            ->select(['egeyear'])
            ->distinct(true)
            ->all(), 'egeyear', 'egeyear');
    }

    public function GetAbitContractListResponse()
    {
        if (!$this->user->userRef) {
            return null;
        }
        return Yii::$app->soapClientAbit->load_with_caching('GetAbitContractList', [
            'AbiturientCode' => $this->user->userRef->reference_id,
            'IdPK' => $this->type->campaign->referenceType->reference_id,
        ]);
    }

    public function updateContractDocsFrom1C(): bool
    {
        $status = true;
        if (!$this->user->userRef) {
            return true;
        }
        try {
            $contracts_result = $this->GetAbitContractListResponse();
            $contracts_result = ToAssocCaster::getAssoc($contracts_result);
            foreach ($this->specialities as $bachelor_speciality) {
                $contracts = ArrayHelper::getValue($contracts_result, 'return.Contract', []);
                if ($contracts) {
                    $bachelor_speciality->buildAndUpdateContractRefFor1C();
                }
            }
        } catch (\Throwable $e) {
            $status = false;
        }
        return $status;
    }

    public function getEgeDisabled(): bool
    {
        return $this->type->hide_ege == 1;
    }

    public function isRequiredCommonFilesAttached(): bool
    {
        return !Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getAllAttachmentsWithoutRegulations()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds(null, ArrayHelper::getValue($this, 'type.campaign.referenceType.reference_uid'))
        );
    }

    public function isEducationDocumentsRequiredFilesAttached(): bool
    {
        
        $educations_with_empty_files = AttachmentManager::GetEntityWithEmptyFilesQuery(EducationData::instance())
            ->andWhere([EducationData::tableName() . '.application_id' => $this->id])
            ->all();
        foreach ($educations_with_empty_files as $education) {
            if ($education->getAttachmentCollection()->isRequired()) {
                return false;
            }
        }
        return true;
    }

    public function isBachelorPreferencesRequiredFilesAttached(): bool
    {
        
        $preferences_with_empty_files = AttachmentManager::GetEntityWithEmptyFilesQuery(BachelorPreferences::instance())
            ->andWhere([BachelorPreferences::tableName() . '.id_application' => $this->id])
            ->all();
        foreach ($preferences_with_empty_files as $preference) {
            if ($preference->getAttachmentCollection()->isRequired()) {
                return false;
            }
        }
        return true;
    }

    public function isBachelorTargetReceptionsRequiredFilesAttached(): bool
    {
        
        $targets_with_empty_files = AttachmentManager::GetEntityWithEmptyFilesQuery(BachelorTargetReception::instance())
            ->andWhere([BachelorTargetReception::tableName() . '.id_application' => $this->id])
            ->all();
        foreach ($targets_with_empty_files as $targetReception) {
            if ($targetReception->getAttachmentCollection()->isRequired()) {
                return false;
            }
        }
        return true;
    }

    public function isIndividualAchievementsRequiredFilesAttached(): bool
    {
        
        $ias_with_empty_files = AttachmentManager::GetEntityWithEmptyFilesQuery(IndividualAchievement::instance())
            ->andWhere([IndividualAchievement::tableName() . '.application_id' => $this->id])
            ->all();
        foreach ($ias_with_empty_files as $individualAchievement) {
            if ($individualAchievement->getAttachmentCollection()->isRequired()) {
                return false;
            }
        }
        return true;
    }

    










    public static function GetExistingAppTypes(User $user): array
    {
        $existing_types = [];
        $response = Yii::$app->soapClientWebApplication->load_with_caching('GetAbiturientCampaigns', [
            "EntrantRef" => UserReferenceTypeManager::GetProcessedUserReferenceType($user),
        ]);
        if (!$response || !isset($response->return) || !isset($response->return->UniversalResponse)) {
            throw new soapException('Failed to fetch campaigns data');
        }
        if (!$response->return->UniversalResponse->Complete) {
            throw new soapException($response->return->UniversalResponse->Description);
        }
        if (isset($response->return->Campaign)) {
            if (!is_array($response->return->Campaign)) {
                $response->return->Campaign = [$response->return->Campaign];
            }
            foreach ($response->return->Campaign as $rawCampaignRef) {
                $campaignRef = ReferenceTypeManager::GetOrCreateReference(StoredAdmissionCampaignReferenceType::class, $rawCampaignRef);
                if ($campaignRef) {
                    $app_type = ApplicationType::find()
                        ->andWhere([ApplicationType::tableName() . '.archive' => false])
                        ->joinWith(['campaign'])
                        ->andWhere([AdmissionCampaign::tableName() . '.archive' => false])
                        ->joinWith(['campaign.referenceType'])
                        ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => $campaignRef->reference_uid])
                        ->one();
                    if ($app_type) {
                        $existing_types[] = $app_type;
                    }
                }
            }
        }
        
        return array_values(ArrayHelper::index($existing_types, 'id'));
    }

    public function beforeArchive()
    {
        if ($this->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            ApplicationAndQuestionaryLinker::linkCurrentActualQuestionary($this);
        }

        $questionaries = $this->getLinkedAbiturientQuestionary()->all();
        foreach ($questionaries as $questionary) {
            $questionary->archive();
        }
    }

    public function entirelyRemoveAppFromOneS(): array
    {
        $code = $this->user->userRef->reference_id;
        $idPK = $this->type->rawCampaign->referenceType->reference_id;

        $request = [
            'AbiturientCode' => $code,
            'IdPK' => $idPK,
            'Entrant' => $this->buildEntrantArray()
        ];
        $result = null;
        try {
            $response = Yii::$app->soapClientAbit->load(
                "RemoveZayavlenie",
                $request
            );
            $result = $response->return;
            if (isset($response->return->UniversalResponse)) {
                $result = $response->return->UniversalResponse;
            }
        } catch (\Throwable $e) {
            $log = [
                'data' => $request,
                'error' => $e->getMessage(),
            ];
            $error_str = PHP_EOL . print_r($log, true);
            Yii::error('Ошибка удаления заявления из ПК: ' . $error_str);
            return [false, 'Не удалось удалить заявление в Информационную систему вуза:' . $error_str];
        }

        if (empty($result)) {
            return [false, 'Не удалось удалить заявление в Информационную систему вуза'];
        }

        return [($result->Complete ?? 0) == 1, ($result->Description ?? '')];
    }

    public function beforeDelete()
    {
        
        if (parent::beforeDelete()) {
            $transaction = Yii::$app->db->beginTransaction();
            $deleteSuccess = true;

            try {
                
                
                $child_drafts = $this->getChildrenDrafts()->all();
                if ($child_drafts) {
                    foreach ($child_drafts as $child) {
                        $child
                            ->setParentDraft(null)
                            ->save(false);
                    }
                }

                $errorFrom = '';

                
                $educations = $this->getRawEducations()->all();
                foreach ($educations as $education) {
                    $deleteSuccess = $education->delete();
                    if (!$deleteSuccess) {
                        $errorFrom .= "{$this->tableName()} -> {$education->tableName()} -> {$education->id}\n";
                    }
                }

                
                if ($deleteSuccess) {
                    $preferences = BachelorPreferences::findAll([
                        'id_application' => $this->id
                    ]);
                    if (!empty($preferences)) {
                        foreach ($preferences as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }
                if ($deleteSuccess) {
                    $targetReceptions = BachelorTargetReception::findAll([
                        'id_application' => $this->id
                    ]);
                    if (!empty($targetReceptions)) {
                        foreach ($targetReceptions as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    $specialities = BachelorSpeciality::find()
                        ->andWhere(['application_id' => $this->id])
                        ->with(['attachments'])
                        ->all();
                    if ($specialities) {
                        foreach ($specialities as $dataToDelete) {
                            foreach ($dataToDelete->attachments as $spec_attatchment) {
                                $spec_attatchment->safeDelete(new User(), false);
                                $spec_attatchment->delete();
                            }
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }
                
                if ($deleteSuccess) {
                    $egeResultIds = $this->getAllEgeResults()
                        ->select('id')
                        ->column();
                    $ct_ids = BachelorResultCentralizedTesting::find()
                        ->where(['egeresult_id' => $egeResultIds])
                        ->select('id')
                        ->column();
                    $date_ids = BachelorDatePassingEntranceTest::find()
                        ->where(['bachelor_egeresult_id' => $egeResultIds])
                        ->select('id')
                        ->column();
                    $child_date_ids = BachelorDatePassingEntranceTest::find()
                        ->where(['parent_id' => $date_ids])
                        ->select('id')
                        ->column();
                    BachelorDatePassingEntranceTest::deleteAll(['id' => array_values(array_unique([...$date_ids, ...$child_date_ids]))]);
                    BachelorResultCentralizedTesting::deleteAll(['id' => $ct_ids]);
                    EgeResult::deleteAll(['id' => $egeResultIds]);
                }

                
                if ($deleteSuccess) {
                    $ias = $this->getRawIndividualAchievements()->all();
                    if (!empty($ias)) {
                        foreach ($ias as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    $allAttachments = $this->getAllAttachments()->all();
                    if (!empty($allAttachments)) {
                        foreach ($allAttachments as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    $commentsComing = $this->getCommentsComing()->all();
                    if (!empty($commentsComing)) {
                        foreach ($commentsComing as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    $moderateHistory = $this->getModerateHistory()->all();
                    if (!empty($moderateHistory)) {
                        foreach ($moderateHistory as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }
                if ($deleteSuccess) {
                    $userRegulations = $this->rawUser->getUserRegulations()
                        ->andWhere(['application_id' => $this->id])
                        ->with(['rawAttachments'])
                        ->all();

                    foreach ($userRegulations as $userRegulation) {
                        $attachments = $userRegulation->rawAttachments;
                        if (!empty($attachments)) {
                            $attachments->safeDelete(new User(), false);
                            $attachments->delete();
                        }
                        $deleteSuccess = $userRegulation->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$userRegulation->tableName()} -> {$userRegulation->id}\n";
                            break;
                        }
                    }
                }
                if ($deleteSuccess) {
                    $questionaries = $this->getLinkedAbiturientQuestionary()->all();
                    foreach ($questionaries as $questionary) {
                        $questionary->delete();
                    }
                }
                
                if ($deleteSuccess) {
                    $history = $this->getHistory()->all();
                    if (!empty($history)) {
                        foreach ($history as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    $change_history_ids = $this->getChangeHistory()->select('id')->column();
                    $change_history_class_ids = ChangeHistoryEntityClass::find()->where(['change_id' => $change_history_ids])->select('id')->column();
                    $change_history_class_input_ids = ChangeHistoryEntityClassInput::find()->where(['entity_class_id' => $change_history_class_ids])->select('id')->column();
                    ChangeHistoryEntityClassInput::deleteAll(['id' => $change_history_class_input_ids]);
                    ChangeHistoryEntityClass::deleteAll(['id' => $change_history_class_ids]);
                    ChangeHistory::deleteAll(['id' => $change_history_ids]);
                }

                if ($deleteSuccess) {
                    $transaction->commit();
                } else {
                    Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
                    $transaction->rollBack();
                }
                return $deleteSuccess;
            } catch (Throwable $e) {
                Yii::error("Ошибка при удалении данных с портала. {$e->getMessage()}");
                $transaction->rollBack();
                return false;
            }
        } else {
            return false;
        }
    }

    public function getSpecialitiesString()
    {
        $specialities = $this->specialities;
        if (!empty($specialities)) {
            $specialityNameList = array_unique(
                array_map(
                    function ($speciality) {
                        

                        $specialityName = ArrayHelper::getValue($speciality, 'speciality.directionRef.reference_name');
                        if (isset($specialityName)) {
                            return $specialityName;
                        }
                        return '';
                    },
                    $specialities
                )
            );
            return implode(', ', $specialityNameList);
        }
        return '';
    }

    public function getFio()
    {
        return ArrayHelper::getValue($this, 'abiturientQuestionary.fio');
    }

    public function getBirthday()
    {
        $questionary = $this->abiturientQuestionary;
        if ($questionary && $questionary->personalData) {
            return strtotime($questionary->personalData->birthdate);
        }
        return null;
    }

    public function getUsermail()
    {
        return ArrayHelper::getValue($this, 'user.email');
    }

    public function getCheckDate(): ?string
    {
        $date = $this->created_at;
        if ($this->synced_with_1C_at) {
            $date = $this->synced_with_1C_at;
        }
        if ($this->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            $date = max($date, $this->approved_at + 1);
        }
        if ($date) {
            return date('Y-m-d\TH:i:s', $date);
        }
        return NeedBlockAndUpdateProcessor::EMPTY_DATE;
    }

    


    public function getCheckConsentDate(): string
    {
        $maxDate = 0;
        $agreementMaxDate = AdmissionAgreement::find()
            ->select('MAX(admission_agreement.sent_at) AS date')
            ->leftJoin('bachelor_speciality', 'admission_agreement.speciality_id = bachelor_speciality.id')
            ->leftJoin('bachelor_application', 'bachelor_speciality.application_id = bachelor_application.id')
            ->leftJoin('agreement_decline', 'agreement_decline.agreement_id = admission_agreement.id')
            ->where([
                'or',
                
                ['admission_agreement.status' => AdmissionAgreement::STATUS_VERIFIED],
                [
                    
                    'and',
                    ['admission_agreement.status' => AdmissionAgreement::STATUS_MARKED_TO_DELETE],
                    [
                        'and',
                        ['not', ['agreement_decline.id' => null]],
                        ['agreement_decline.sent_at' => [null, 0]],
                    ],
                ]
            ])
            ->andWhere(['not', ['admission_agreement.sent_at' => [null, 0]]])
            ->andWhere(['application_id' => $this->id])
            ->scalar();

        $potentialMaxDate = (int)$agreementMaxDate;
        if (!empty($potentialMaxDate)) {
            $maxDate = max($maxDate, $potentialMaxDate);
        }
        $agreementMaxDate = AgreementDecline::find()
            ->select('MAX(agreement_decline.sent_at) AS date')
            ->leftJoin('admission_agreement', 'agreement_decline.agreement_id = admission_agreement.id')
            ->leftJoin('bachelor_speciality', 'admission_agreement.speciality_id = bachelor_speciality.id')
            ->leftJoin('bachelor_application', 'bachelor_speciality.application_id = bachelor_application.id')
            ->where(['not', ['agreement_decline.sent_at' => [null, 0]]])
            ->andWhere(['application_id' => $this->id])
            ->scalar();

        $potentialMaxDate = (int)$agreementMaxDate;
        if (!empty($potentialMaxDate)) {
            $maxDate = max($maxDate, $potentialMaxDate);
        }

        $potentialMaxDate = AllAgreementsHandler::MaxConsentDate($this);
        if (!empty($potentialMaxDate)) {
            $maxDate = max($maxDate, $potentialMaxDate);
        }

        if (!empty($maxDate)) {
            return date('Y-m-d\TH:i:s', $maxDate);
        }
        return NeedBlockAndUpdateProcessor::EMPTY_DATE;
    }

    public function addModerateHistory(ActiveRecord $initiator)
    {
        $user = null;
        if ($initiator instanceof User) {
            $user = $initiator;
        } elseif ($initiator instanceof EntrantManager) {
            $user = $initiator->localManager;
        } elseif ($initiator instanceof MasterSystemManager) {
            $user = $initiator->getEntrantManagerEntity()->localManager;
        }
        if (!$user) {
            Yii::warning("Не удалось определить инициатора модерации: " . print_r($initiator, true), 'addModerateHistory');
        }
        if ($this->status == self::STATUS_APPROVED || $this->status == self::STATUS_NOT_APPROVED) {
            $moderate_history = new ModerateHistory();
            $moderate_history->application_id = $this->id;
            $moderate_history->status = $this->status;
            $moderate_history->comment = $this->moderator_comment;
            $moderate_history->user_id = ($user->id ?? $this->approver_id);
            $moderate_history->moderated_at = time();
            if (!$moderate_history->save()) {
                throw new RecordNotValid($moderate_history);
            }
        }
    }

    






    public function addApplicationHistory(int $type): bool
    {
        $attributes = [
            'application_id' => $this->id,
            'type' => $type,
        ];

        $application_history = ApplicationHistory::findOne($attributes);
        if (!$application_history) {
            $application_history = new ApplicationHistory($attributes);
        }

        return $application_history->save();
    }

    public function load($data, $formName = null)
    {
        if (parent::load($data, $formName)) {
            $this->moderator_comment = trim((string)$this->moderator_comment ?? '') ?: null;
            return true;
        }
        return false;
    }

    public function haveNotDeleteRevertableSpec()
    {
        foreach ($this->specialities as $spec) {
            if ($spec->isDeleteRevertable() === false) {
                return true;
            }
        }
        return false;
    }

    public function getAdditionalAttachmentsInfo(): array
    {
        $files = [];

        $attachments = Attachment::find()
            ->joinWith('attachmentType attachment_type')
            ->andWhere([
                'attachment_type.system_type' => AttachmentType::SYSTEM_TYPE_COMMON,
                'attachment_type.hidden' => false,
                Attachment::tableName() . '.application_id' => $this->id,
                Attachment::tableName() . '.deleted' => false,
            ])->all();

        foreach ($attachments as $attachment) {
            $files[] = [
                $attachment,
                ArrayHelper::getValue($attachment, 'attachmentType.documentType'),
                ArrayHelper::getValue($attachment, 'attachmentType.name')
            ];
        }
        $questionary = $this->abiturientQuestionary;

        return [...$files, ...($questionary ? $questionary->getAdditionalAttachmentsInfo() : []), ...$this->getApplicationReturnFilesInfo()];
    }

    public function getRegulationsAttachmentsInfo(): array
    {
        
        $files = [];
        $regulations = $this->getRegulations()->with(['attachments'])->all();
        $regulations = [...$regulations, ...($this->abiturientQuestionary ? $this->abiturientQuestionary->getUserRegulations()->with(['attachments'])->all() : [])];
        $regulations = [...$regulations, ...$this->user->getCleanUserRegulations()->with(['attachments'])->all()];
        foreach ($regulations as $regulation) {
            foreach ($regulation->attachments as $attachment) {
                $files[] = [
                    $attachment,
                    ArrayHelper::getValue($attachment, 'attachmentType.documentType'),
                    ArrayHelper::getValue($attachment, 'attachmentType.name')
                ];
            }
        }
        return $files;
    }

    public function getConsentFilesInfo(): array
    {
        $files = [];

        foreach ($this->specialities as $speciality) {
            $files = [...$files, ...$speciality->getConsentFileInfo()];
        }
        return $files;
    }

    public function getSpecificEntitiesFilesInfo(): array
    {
        return [
            ...$this->getEducationFilesInfo(),
            ...$this->getBenefitFilesInfo(),
            ...$this->getOlympiadFilesInfo(),
            ...$this->getTargetReceptionFilesInfo(),
            ...$this->getPaidContractFilesInfo(),
            ...$this->getAchievementFilesInfo(),
        ];
    }

    public function getPaidContractFilesInfo(): array
    {
        $files = [];
        foreach ($this->specialities as $speciality) {
            $files = [...$files, ...$speciality->getPaidContractFilesInfo()];
        }
        return $files;
    }

    public function getOlympiadFilesInfo(): array
    {
        $files = [];

        $admission_procedures = $this->bachelorPreferencesOlymp;
        foreach ($admission_procedures as $procedure) {
            $files = [...$files, ...$procedure->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function getEducationFilesInfo(): array
    {
        $files = [];

        $educations = $this->educations;
        foreach ($educations as $education) {
            $files = [...$files, ...$education->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function getBenefitFilesInfo(): array
    {
        $files = [];

        $admission_procedures = $this->bachelorPreferencesSpecialRight;
        foreach ($admission_procedures as $procedure) {
            $files = [...$files, ...$procedure->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function getTargetReceptionFilesInfo(): array
    {
        $files = [];
        $targets = BachelorTargetReception::find()
            ->where(['id_application' => $this->id])
            ->all();
        foreach ($targets as $target) {
            $files = [...$files, ...$target->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function getAchievementFilesInfo(): array
    {
        $files = [];
        
        $individual_achievements = $this->getRawIndividualAchievements()->all();

        foreach ($individual_achievements as $individual_achievement) {
            $files = [...$files, ...$individual_achievement->getAttachedFilesInfo()];
        }
        return $files;
    }

    public function getAllAttachmentsInfo(): array
    {
        return [
            ...($this->abiturientQuestionary ? $this->abiturientQuestionary->getSpecificEntitiesFilesInfo() : []),
            ...$this->getSpecificEntitiesFilesInfo(),
            ...$this->getConsentFilesInfo(),
            ...$this->getAdditionalAttachmentsInfo(),
            ...$this->getRegulationsAttachmentsInfo(),
        ];
    }

    public function getApplicationReturnFilesInfo(): array
    {
        $doc_type = DocumentType::findByUID(Yii::$app->configurationManager->getCode('application_document_type_guid'));

        $files = [];
        foreach ($this->applicationReturnAttachments as $attachment) {
            $files[] = [
                $attachment,
                $doc_type,
                null
            ];
        }

        return $files;
    }

    




    public function getBachelorPreferencesOlymp()
    {
        $tn = BachelorPreferences::tableName();
        return $this->getPreferences()->andOnCondition(['not', ["{$tn}.olympiad_id" => null]]);
    }

    


    public function getRawBachelorPreferencesOlymp()
    {
        $tn = BachelorPreferences::tableName();
        return $this->getRawPreferences()->andOnCondition(['not', ["{$tn}.olympiad_id" => null]]);
    }

    




    public function getBachelorPreferencesSpecialRight()
    {
        $tn = BachelorPreferences::tableName();
        return $this->getPreferences()->andOnCondition(["{$tn}.olympiad_id" => null]);
    }

    


    public function getRawBachelorPreferencesSpecialRight()
    {
        $tn = BachelorPreferences::tableName();
        return $this->getRawPreferences()->andOnCondition(["{$tn}.olympiad_id" => null]);
    }

    




    public function getBachelorTargetReceptions()
    {
        return $this->getTargetReceptions();
    }

    


    public function getTargetReceptions()
    {
        return $this->getRawTargetReceptions()->andOnCondition([BachelorTargetReception::tableName() . '.archive' => false]);
    }

    


    public function getRawTargetReceptions()
    {
        return $this->hasMany(BachelorTargetReception::class, ['id_application' => 'id']);
    }

    


    public function getPreferences()
    {
        return $this->getRawPreferences()->active();
    }

    


    public function getRawPreferences()
    {
        return $this->hasMany(BachelorPreferences::class, ['id_application' => 'id']);
    }

    


    public function getAvailableCgetEntranceTestSetIds()
    {
        $bachelorSpecialityTableName = BachelorSpeciality::tableName();
        return $this->getSpecialities()
            ->select(["{$bachelorSpecialityTableName}.cget_entrance_test_set_id"])
            ->andWhere(['IS NOT', "{$bachelorSpecialityTableName}.cget_entrance_test_set_id", null]);
    }

    public function haveArchivedEge()
    {
        foreach ($this->egeResults as $res) {
            if ($res->cgetDiscipline->archive) {
                return true;
            }
        }
        return false;
    }

    public function haveArchivedSpeciality()
    {
        foreach ($this->specialities as $spec) {
            if ($spec->speciality->archive) {
                return true;
            }
        }
        return false;
    }

    public function haveEgeConflicts()
    {
        return $this->haveArchivedEge();
    }

    public function hasSpeciality(int $specialityId): bool
    {
        return $this->getSpecialities()->andWhere(['speciality_id' => $specialityId])->exists();
    }

    




    public function archiveMarkedAgreementsToDelete()
    {
        $ids = $this->getSpecialities()
            ->select([AdmissionAgreementToDelete::tableName() . '.id'])
            ->joinWith(['rawAgreements'])
            ->andWhere(['not', [BachelorSpeciality::tableName() . '.application_code' => null]]);

        $agreementsToDelete = AdmissionAgreementToDelete::find()
            ->where([AdmissionAgreementToDelete::tableName() . '.id' => $ids])
            ->andWhere([AdmissionAgreementToDelete::tableName() . '.archive' => false])
            ->all();

        foreach ($agreementsToDelete as $agreementToDelete) {
            $agreement = $agreementToDelete->agreement;

            if (isset($agreement->agreementDecline)) {
                
                $agreement->agreementDecline->sent_at = time();
                $agreement->agreementDecline->save(false, ['sent_at']);
            }

            $agreementToDelete->archive = true;
            $agreementToDelete->save(false);
        }
        return true;
    }

    public function markApplicationRemoved()
    {
        DraftsManager::clearOldSendings($this, IdentityManager::GetIdentityForHistory(), DraftsManager::REASON_RETURN);
        DraftsManager::clearOldModerations($this, IdentityManager::GetIdentityForHistory(), DraftsManager::REASON_RETURN);
        DraftsManager::removeOldApproved($this, IdentityManager::GetIdentityForHistory(), DraftsManager::REASON_RETURN);

        $this
            ->setArchiveInitiator(IdentityManager::GetIdentityForHistory())
            ->setArchiveReason(DraftsManager::REASON_RETURN)
            ->archive();
    }

    





    public function isIn1CByModerateHistory()
    {
        return $this->getModerateHistory()->andWhere(['status' => self::STATUS_APPROVED])->exists();
    }

    public function isIn1C(): bool
    {
        return $this->user && $this->type && $this->user->hasAppInOneS($this->type);
    }

    


    public function haveUnstagedDisciplineSet(): bool
    {
        
        
        $abiturientPriorityTotalizer = $this->getAbiturientPriorityTotalizer();

        
        
        $realPriorityTotalizer = $this->getRealPriorityTotalizer();

        
        return (new Query)
            ->select([
                'abiturient_priority_totalizer.abit_bachelor_speciality_id as bachelor_speciality__id',
                'real_priority_totalizer.real_priority_sum',
                'abiturient_priority_totalizer.abit_priority_sum'
            ])
            ->from(['real_priority_totalizer' => $realPriorityTotalizer])
            ->leftJoin(
                ['abiturient_priority_totalizer' => $abiturientPriorityTotalizer],
                'real_priority_totalizer.real_bachelor_speciality_id = abiturient_priority_totalizer.abit_bachelor_speciality_id'
            )
            
            
            
            ->andHaving([
                'OR',
                ['IN', 'abiturient_priority_totalizer.abit_priority_sum', [0, null]],
                'real_priority_totalizer.real_priority_sum > abiturient_priority_totalizer.abit_priority_sum',
            ])
            ->groupBy([
                'bachelor_speciality__id',
                'real_priority_totalizer.real_priority_sum',
                'abiturient_priority_totalizer.abit_priority_sum'
            ])
            ->exists();
        
    }

    




    private function getSpecialitiesWithoutEntranceTests(): ActiveQuery
    {
        return $this->getRawSpecialities()
            ->active()
            ->andOnCondition([BachelorSpeciality::tableName() . '.is_without_entrance_tests' => false]);
    }

    








    private function getAbiturientPriorityTotalizer(): ActiveQuery
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        $archiveColumnBachelorEntranceTestSet = BachelorEntranceTestSet::getArchiveColumn();

        
        $setsPriorityTotalizerQuery = BachelorEntranceTestSet::find()
            ->select([
                "{$tnBachelorEntranceTestSet}.priority as new_priority",
                "{$tnBachelorEntranceTestSet}.bachelor_speciality_id"
            ])
            ->joinWith('bachelorSpeciality')
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $this->id])
            ->andWhere([
                'OR',
                ["{$tnBachelorEntranceTestSet}.{$archiveColumnBachelorEntranceTestSet}" => null],
                ["{$tnBachelorEntranceTestSet}.{$archiveColumnBachelorEntranceTestSet}" => false]
            ])
            ->groupBy([
                'new_priority',
                "{$tnBachelorEntranceTestSet}.bachelor_speciality_id"
            ]);

        
        
        return $this->getSpecialitiesWithoutEntranceTests()
            ->select([
                "{$tnBachelorSpeciality}.id AS abit_bachelor_speciality_id",
                'SUM(bachelor_entrance_test_set_v2.new_priority) AS abit_priority_sum'
            ])
            ->leftJoin(
                ['bachelor_entrance_test_set_v2' => $setsPriorityTotalizerQuery],
                "{$tnBachelorSpeciality}.id = bachelor_entrance_test_set_v2.bachelor_speciality_id"
            )
            ->groupBy(["abit_bachelor_speciality_id"]);
    }

    






    private function getRealPriorityTotalizer(): Query
    {
        $tnEntranceTest = CgetEntranceTest::tableName();
        $tnEntranceTestSet = CgetEntranceTestSet::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnCompetitiveGroupEntranceTest = DictionaryCompetitiveGroupEntranceTest::tableName();

        
        $setsPriorityTotalizerQuery = $this->getSpecialitiesWithoutEntranceTests()
            ->select([
                "{$tnBachelorSpeciality}.id AS bachelor_speciality__id",
                "{$tnEntranceTest}.priority AS new_priority"
            ])
            ->joinWith(['speciality speciality' => function (ActiveQuery $query) {
                $query->joinWith([
                    'dictionaryCompetitiveGroupEntranceTests' => function (ActiveQuery $query) {
                        $query->joinWith(['cgetEntranceTestSets' => function (ActiveQuery $query) {
                            $query
                                ->joinWith('educationTypeRef cget_education_type_ref')
                                ->joinWith('profileRef cget_profile_ref')
                                ->joinWith('rawEntranceTests');
                        }]);
                    }
                ]);
            }])
            ->joinWith(['educationsData' => function (ActiveQuery $query) {
                $query
                    ->joinWith('educationType education_type')
                    ->joinWith('profileRef profile_ref');
            }])
            ->andWhere([
                "{$tnEntranceTest}.archive" => false,
                "{$tnEntranceTestSet}.archive" => false,
                "{$tnCompetitiveGroupEntranceTest}.archive" => false,
            ])
            ->andWhere([
                'OR',
                ['cget_education_type_ref.id' => null],
                [
                    'AND',
                    ['cget_education_type_ref.archive' => false],
                    new Expression('cget_education_type_ref.ref_key = education_type.ref_key')
                ],
            ])
            ->andWhere([
                'OR',
                ['cget_profile_ref.id' => null],
                [
                    'AND',
                    ['cget_profile_ref.archive' => false],
                    new Expression('cget_profile_ref.reference_uid = profile_ref.reference_uid')
                ],
            ])
            ->groupBy([
                'new_priority',
                'bachelor_speciality__id'
            ]);
        
        
        return (new Query)
            ->select([
                'cget_entrance_test_v2.bachelor_speciality__id AS real_bachelor_speciality_id',
                'SUM(cget_entrance_test_v2.new_priority) AS real_priority_sum'
            ])
            ->from(['cget_entrance_test_v2' => $setsPriorityTotalizerQuery])
            ->groupBy(['real_bachelor_speciality_id']);
    }

    





    public function validateUnstagedDisciplineSets()
    {
        if ($this->type->hide_ege) {
            return true;
        }

        return !$this->haveUnstagedDisciplineSet();
    }

    





    public function validateUnstagedDisciplineResults()
    {
        if ($this->type->hide_ege) {
            return true;
        }

        return !$this->haveUnstagedDisciplineResult();
    }

    public function haveUnstagedDisciplineResult()
    {
        $activeEgeExists = $this->getEgeResults()->exists();
        if ($activeEgeExists) {
            return $this->getEgeResults()
                ->andWhere(['status' => EgeResult::STATUS_UNSTAGED])
                ->exists();
        }

        return boolval(array_filter($this->specialities, function (BachelorSpeciality $spec) {
            return !$spec->getIsWithoutEntranceTests();
        }));
    }

    




    public function getRawIndividualAchievements()
    {
        return $this->hasMany(IndividualAchievement::class, ['application_id' => 'id']);
    }

    public function getIndividualAchievements()
    {
        return $this->getRawIndividualAchievements()
            ->andOnCondition([
                'NOT IN',
                IndividualAchievement::tableName() . '.status',
                [
                    IndividualAchievement::STATUS_ARCHIVED,
                    IndividualAchievement::STATUS_TO_DELETE,
                ]
            ]);
    }

    public function getIndividualAchievementsWithSameLimitedGroup(): ActiveQuery
    {
        
        $group_ids = $this->getIndividualAchievements()
            ->select(['achievementGroupRef.reference_uid'])
            ->innerJoinWith(['achievementType achievementType' => function ($q) {
                $q->innerJoinWith(['achievementGroupRef achievementGroupRef']);
            }])
            ->andWhere(['achievementType.points_in_group_are_awarded_once' => true])
            ->groupBy('achievementGroupRef.reference_uid')
            ->having('COUNT(*) > 1');

        return $this->getIndividualAchievements()
            ->innerJoinWith(['achievementType achievementType' => function ($q) {
                $q->innerJoinWith(['achievementGroupRef achievementGroupRef']);
            }])
            ->andWhere(['achievementGroupRef.reference_uid' => $group_ids]);
    }

    public function getLastAppliedHistory()
    {
        return $this->getModerateHistory()
            ->andWhere([
                'status' => self::STATUS_APPROVED
            ])
            ->orderBy([
                'moderated_at' => SORT_DESC
            ])
            ->limit(1)
            ->one();
    }

    public function getFirstAppliedHistory()
    {
        return $this->getModerateHistory()
            ->andWhere([
                'status' => self::STATUS_APPROVED
            ])
            ->orderBy([
                'moderated_at' => SORT_ASC
            ])
            ->limit(1)
            ->one();
    }

    


    private function getHaveAttachedAgreementQuery(): ActiveQuery
    {
        return $this->getSpecialities()->joinWith('agreement')
            ->where(['!=', 'admission_agreement.status', AdmissionAgreement::STATUS_MARKED_TO_DELETE]);
    }

    public function haveAttachedAgreementExcludeNonBudget(): bool
    {
        return $this->getHaveAttachedAgreementQuery()
            ->joinWith('speciality.educationSourceRef education_source_ref')
            ->andWhere(['NOT', ['education_source_ref.reference_uid' => BachelorSpeciality::getCommercialBasis()]])
            ->exists();
    }

    public function haveAttachedAgreement()
    {
        return $this->getHaveAttachedAgreementQuery()->exists();
    }

    



    public function sendAllApplicationTo1C()
    {
        $this->archiveAdmissionCampaignHandler->handle();
        $status = false;
        foreach ($this->applyingSteps as $step) {
            $status = $step->makeStep();
            if (!$status) {
                break;
            }
        }

        if ($status) {
            $this->status = BachelorApplication::STATUS_APPROVED;
            $this->unblockApplication(false);
            $manager = null;
            if (Yii::$app->user->identity->isModer()) {
                $manager = Yii::$app->user->identity;
                $this->approver_id = $manager->id;
            }
            $this->approved_at = time();
            $this->setupSyncData();
            if (!$this->save()) {
                throw new RecordNotValid($this);
            }
            if ($manager) {
                $this->addModerateHistory($manager);
            }
            ApplicationHistory::deleteAll(['application_id' => $this->id]);
            $this->getSandboxSendHandler()->updateDataAfterSuccessSending();
        } else {
            $this->status = BachelorApplication::STATUS_REJECTED_BY1C;
            $this->save();
        }
        return $status;
    }

    public function setupSyncData()
    {
        $this->synced_with_1C_at = time();
    }

    








    public function getRegulations($relatedEntity = null): ActiveQuery
    {
        return UserRegulationRepository::GetUserRegulationsByApplicationAndRelatedEntity($this, $relatedEntity);
    }

    



    public function getChangeHistory()
    {
        return $this->hasMany(
            ChangeHistory::class,
            ['application_id' => 'id']
        )
            ->with([
                'changeHistoryEntityClasses',
                'changeHistoryEntityClasses.changeHistoryEntityClassInputs'
            ]);
    }

    public function getChangeHistoryOrderedById()
    {
        return $this->getChangeHistory()->orderBy([ChangeHistory::tableName() . '.id' => SORT_ASC]);
    }

    public static function buildEntrantArrayFromData(User $user, ApplicationType $type)
    {
        $campaign = $type->campaign;

        return [
            'EntrantRef' => UserReferenceTypeManager::GetProcessedUserReferenceType($user),
            'CampaignRef' => ReferenceTypeManager::GetReference($campaign, 'referenceType'),
            'EntrantPortalGUID' => $user->system_uuid,
        ];
    }

    public function buildEntrantArray()
    {
        return BachelorApplication::buildEntrantArrayFromData($this->user, $this->type);
    }

    public function getSandboxSendHandler(): IApplicationSendHandler
    {
        return $this->sandboxHandler;
    }

    public function getNonSandboxSendHandler(): IApplicationSendHandler
    {
        return $this->nonSandboxHandler;
    }

    public function resetStatus(bool $save = true): bool
    {
        if ($this->draft_status == IDraftable::DRAFT_STATUS_CREATED) {
            $this->status = BachelorApplication::STATUS_CREATED;
            $this->sent_at = null;
            $this->approved_at = null;
            $this->approver_id = null;

            if ($save) {
                return $this->save();
            }
            return true;
        }
        return false;
    }

    


    public function getBlockerEntrantManager()
    {
        return $this->hasOne(EntrantManager::class, ['id' => 'entrant_manager_blocker_id']);
    }

    




    public function blockApplication(EntrantManager $blocker)
    {
        $this->block_status = BachelorApplication::BLOCK_STATUS_ENABLED;
        $this->entrant_manager_blocker_id = $blocker->id;
        if (!is_null($blocker->localManager)) {
            $this->blocker_id = $blocker->localManager->id;
        }

        $this->save();
    }

    




    public function getBlocker()
    {
        return $this->hasOne(User::class, ['id' => 'blocker_id']);
    }

    




    public function unblockApplication(bool $saveApplication = true)
    {
        $this->block_status = BachelorApplication::BLOCK_STATUS_DISABLED;
        $this->entrant_manager_blocker_id = null;
        $this->blocker_id = null;

        if ($saveApplication) {
            if (!$this->save(true, ['block_status', 'entrant_manager_blocker_id', 'blocker_id'])) {
                throw new RecordNotValid($this);
            }
        }
    }

    public function fullyUnblockApplication()
    {
        
        $sent_app = DraftsManager::getApplicationDraftByOtherDraft($this, IDraftable::DRAFT_STATUS_SENT);
        $moderating_app = DraftsManager::getApplicationDraftByOtherDraft($this, IDraftable::DRAFT_STATUS_MODERATING);
        if ($sent_app) {
            $sent_app->unblockApplication();
        }
        if ($moderating_app) {
            $moderating_app->unblockApplication();
        }
    }

    









    public function isApplicationBlocked(): array
    {
        $baseCondition = ($this->block_status == self::BLOCK_STATUS_ENABLED && ($this->entrant_manager_blocker_id !== null || $this->blocker_id !== null));
        $currentUser = \Yii::$app->user->identity;

        if (!$baseCondition) {
            return [false, 0];
        }

        [$blocked, $time_until_unblock] = $this->checkBlockByTimeout();
        if (!$currentUser->isModer()) {
            return [$blocked, $time_until_unblock];
        }

        if (!$blocked) {
            return [false, 0];
        }

        $isMasterSystemManagerEnabled = Yii::$app->configurationManager->getMasterSystemManagerSetting('use_master_system_manager_interface');
        if ($currentUser instanceof IEntrantManager) {
            if (!is_null($this->entrant_manager_blocker_id)) {
                
                if ($isMasterSystemManagerEnabled && $this->blockerEntrantManager->isLocalManager()) {
                    $this->unblockApplication();

                    return [false, 0];
                }
                if (!$isMasterSystemManagerEnabled && !$this->blockerEntrantManager->isLocalManager()) {
                    $this->unblockApplication();

                    return [false, 0];
                }

                $entrantManager = $currentUser->getEntrantManagerEntity();
                if ($this->entrant_manager_blocker_id !== $entrantManager->id) {
                    $fieldToSearch = $currentUser instanceof User ? 'local_manager' : 'master_system_manager';
                    if ($this->blockerEntrantManager->{$fieldToSearch} === $entrantManager->{$fieldToSearch}) {
                        return [false, 0]; 
                    }

                    return [true, $time_until_unblock];
                } else {
                    return [false, 0];
                }
            }
        }

        if ($currentUser instanceof User) {
            if ($this->blocker_id === $currentUser->id) {
                return [false, 0]; 
            }
        }

        return [true, $time_until_unblock];
    }

    




    public function getBlockerName(): string
    {
        if (!is_null($this->entrant_manager_blocker_id)) {
            $entrantManager = $this->blockerEntrantManager;
            if (!is_null($entrantManager)) {
                return $entrantManager->getManagerName();
            }
        }

        if (!is_null($this->blocker_id)) {
            $user = User::findOne($this->blocker_id);
            return $user->username;
        }

        return '';
    }

    


    public function isPrintApplicationByFullPackageAvailable()
    {
        if (!$this->specialities) {
            return false;
        }

        foreach ($this->specialities as $spec) {
            if (!$spec->educationsData) {
                return false;
            }
        }

        return true;
    }

    




    public function getSavedEgeResults(): array
    {
        $savedResults = $this->getEgeResults()->all();

        foreach ($savedResults as $savedResult) {
            
            $savedResult->setScenario(EgeResult::SCENARIO_SAVE_SETTINGS);
        }

        return $savedResults;
    }


    public function clearApplicationCache()
    {
        Yii::$app->soapClientAbit->resetCurrentUserCache('GetAbiturientCampaigns', [$this->user_id]);
        Yii::$app->soapClientWebApplication->resetCurrentUserCache('GetEntrantPackage', [$this->user_id]);
        Yii::$app->soapClientWebApplication->resetCurrentUserCache('GetEntrantProfilePackage', [$this->user_id]);

        Yii::$app->soapClientAbit->resetCurrentUserCache('NeedBlockAndUpdate', [$this->user_id]);
        \Yii::$app->soapClientAbit->resetCurrentUserCache('GetReference', [$this->user_id]); 
        Yii::$app->soapClientAbit->resetCurrentUserCache('GetAbitContractList', [$this->user_id]);
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    protected $_order_info = -1;

    public function getOrderInfo()
    {
        if ($this->_order_info !== -1) {
            return $this->_order_info;
        }

        $this->_order_info = null;

        
        $actual_app = DraftsManager::getActualApplication($this->user, $this->type);
        if ($actual_app && $actual_app->have_order) {
            $this->_order_info = trim((string)$actual_app->order_info);
        }
        return $this->_order_info;
    }

    




    public function hasAnyNotVerifiedAgreementEntity(): bool
    {
        $specialities = $this->specialities;
        foreach ($specialities as $speciality) {
            
            $agreements = $speciality->getRawAgreements()
                ->andWhere(['not', [AdmissionAgreement::tableName() . '.status' => AdmissionAgreement::STATUS_VERIFIED]])
                ->all();
            foreach ($agreements as $agreement) {
                if ($agreement->status == AdmissionAgreement::STATUS_NOTVERIFIED) {
                    return true;
                }
                if ($agreement->status == AdmissionAgreement::STATUS_MARKED_TO_DELETE && $agreement->getAgreementDecline()->andWhere([AgreementDecline::tableName() . '.sent_at' => [null, 0]])->exists()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToManyRelationPresenter('educations', [
                'parent_instance' => $this,
                'child_class' => EducationData::class,
                'actual_relation_name' => 'rawEducations',
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
            new OneToManyRelationPresenter('targetReceptions', [
                'parent_instance' => $this,
                'actual_relation_name' => 'rawTargetReceptions',
                'child_class' => BachelorTargetReception::class,
                'find_exists_child' => false,
                'child_column_name' => 'id_application',
            ]),
            new OneToManyRelationPresenter('preferences', [
                'parent_instance' => $this,
                'child_class' => BachelorPreferences::class,
                'find_exists_child' => false,
                'child_column_name' => 'id_application',
                'actual_relation_name' => 'rawBachelorPreferencesSpecialRight',
            ]),
            new OneToManyRelationPresenter('olympiads', [
                'parent_instance' => $this,
                'child_class' => BachelorPreferences::class,
                'find_exists_child' => false,
                'child_column_name' => 'id_application',
                'actual_relation_name' => 'rawBachelorPreferencesOlymp',
            ]),
            new OneToManyRelationPresenter('bachelorSpecialities', [
                'parent_instance' => $this,
                'child_class' => BachelorSpeciality::class,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
                'actual_relation_name' => 'rawSpecialities',
            ]),
            new OneToManyRelationPresenter('userRegulations', [
                'parent_instance' => $this,
                'child_class' => UserRegulation::class,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
            new OneToManyRelationPresenter('individualAchievements', [
                'parent_instance' => $this,
                'child_class' => IndividualAchievement::class,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
            new OneToManyRelationPresenter('commentsComing', [
                'parent_instance' => $this,
                'child_class' => CommentsComing::class,
                'child_column_name' => 'bachelor_application_id',
                'find_exists_child' => false,
                'actual_relation_name' => 'commentsComing',
                'ignore_in_comparison' => true,
            ]),
            new OneToManyRelationPresenter('applicationHistory', [
                'parent_instance' => $this,
                'child_class' => ApplicationHistory::class,
                'actual_relation_name' => 'history',
                'ignore_in_comparison' => true,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
            new OneToManyRelationPresenter('moderationHistory', [
                'parent_instance' => $this,
                'child_class' => ModerateHistory::class,
                'actual_relation_name' => 'moderateHistory',
                'ignore_in_comparison' => true,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
            new OneToManyRelationPresenter('agreementRecords', [
                'parent_instance' => $this,
                'child_class' => AgreementRecord::class,
                'actual_relation_name' => 'agreementRecords',
                'child_column_name' => 'application_id',
                'ignore_in_comparison' => true
            ]),
            new ManyToManyRelationPresenter('linkedAbiturientQuestionaries', [
                'parent_instance' => $this,
                'parent_column_name' => 'id',
                'child_class' => AbiturientQuestionary::class,
                'child_column_name' => 'id',
                'via_table' => '{{%application_and_questionary_junction}}',
                'via_table_parent_column' => 'application_id',
                'via_table_child_column' => 'questionary_id',
                'ignore_in_comparison' => true
            ]),
            new OneToManyRelationPresenter('egeResults', [
                'parent_instance' => $this,
                'child_class' => EgeResult::class,
                'find_exists_child' => false,
                'child_column_name' => 'application_id',
            ]),
        ];
    }

    public function getLinkedAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id'])
            ->viaTable('{{%application_and_questionary_junction}}', [
                'application_id' => 'id'
            ]);
    }

    public function setQuestionaryAsApproved()
    {
        


        $questionary = $this->getAbiturientQuestionary()->one();
        if ($questionary && $questionary->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            return;
        }
        
        $questionary->status = AbiturientQuestionary::STATUS_APPROVED;
        
        
        $questionary->approver_id = \Yii::$app->user->identity->id;
        $questionary->approved_at = time();
        $questionary
            ->loadDefaultValues()
            ->save(false);
        ApplicationAndQuestionaryLinker::copyQuestionaryToActual($questionary);
    }

    public function getAbiturientQuestionary()
    {
        if ($this->isDraftInSendMode() || $this->isArchivedApprovedDraft()) {
            $query = $this->getLinkedAbiturientQuestionary();
            if ($query->exists()) {
                return $query;
            }
        }
        if ($this->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            $actual_questionary_query = $this->hasOne(AbiturientQuestionary::class, ['user_id' => 'user_id'])
                ->active()
                ->andWhere([AbiturientQuestionary::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_APPROVED]);
            if (!$actual_questionary_query->exists() && $this->user) {
                
                DraftsManager::getActualQuestionary($this->user);
            }
            return $actual_questionary_query;
        }

        return $this->hasOne(AbiturientQuestionary::class, ['user_id' => 'user_id'])
            ->active()
            ->andWhere([AbiturientQuestionary::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED]);
    }

    










    private function createIa($ia): int
    {
        $ia_hash_table = ToAssocCaster::getAssoc($ia);
        return IndividualAchievement::GetOrCreateFromRaw(
            ArrayHelper::getValue($ia_hash_table, 'DocumentSeries'),
            ArrayHelper::getValue($ia_hash_table, 'DocumentNumber'),
            ArrayHelper::getValue($ia_hash_table, 'DocumentDate'),
            ArrayHelper::getValue($ia_hash_table, 'Organization'),
            '',
            $this,
            ArrayHelper::getValue($ia_hash_table, 'AchievementCategoryRef'),
            ArrayHelper::getValue($ia_hash_table, 'AchievementDocumentTypeRef')
        )->id;
    }

    public function hasCreatedDraft(): bool
    {
        return !!DraftsManager::getApplicationDraftByOtherDraft($this, IDraftable::DRAFT_STATUS_CREATED);
    }

    public function setArchiveInitiator($initiator): IArchiveWithInitiator
    {
        if ($initiator instanceof User) {
            $this->archived_by_user_id = $initiator->id;
        } elseif ($initiator instanceof EntrantManager) {
            $this->archived_by_entrant_manager_id = $initiator->id;
        } elseif ($initiator instanceof MasterSystemManager) {
            $this->archived_by_entrant_manager_id = $initiator->getEntrantManagerEntity()->id;
        }
        return $this;
    }

    public function getArchiveInitiator()
    {
        if ($this->archived_by_user_id) {
            return User::findOne($this->archived_by_user_id);
        }
        if ($this->archived_by_entrant_manager_id) {
            return EntrantManager::findOne($this->archived_by_entrant_manager_id);
        }
        return null;
    }

    public function getArchiveInitiatorName()
    {
        $archiveInitiator = $this->archiveInitiator;
        if (!$archiveInitiator) {
            return '';
        }

        $archiveInitiatorName = '';
        if ($archiveInitiator instanceof User) {
            $archiveInitiatorName = $archiveInitiator->fullName;
        } elseif ($archiveInitiator instanceof EntrantManager) {
            $archiveInitiatorName = $archiveInitiator->managerName;
        }

        return $archiveInitiatorName;
    }

    public function setArchiveReason(string $reason): IArchiveWithInitiator
    {
        if (!in_array($reason, array_keys(DraftsManager::ARCHIVE_REASONS))) {
            throw new InvalidArgumentException('Не корректная причина архивации');
        }
        $this->archive_reason = $reason;
        return $this;
    }

    


    public function getArchiveReason(): string
    {
        return ArrayHelper::getValue(DraftsManager::ARCHIVE_REASONS, $this->archive_reason, '');
    }

    


    public function getParentDraft(): ?IDraftable
    {
        if (EmptyCheck::isEmpty($this->parent_draft_id)) {
            return null;
        }
        return BachelorApplication::find()->where(['id' => $this->parent_draft_id])->one();
    }

    




    public function setParentDraft(?IDraftable $draftable): IArchiveWithInitiator
    {
        $this->parent_draft_id = ArrayHelper::getValue($draftable, 'id');
        return $this;
    }

    


    public function hasChildrenDrafts(): bool
    {
        return $this->getChildrenDrafts()->exists();
    }

    


    public function getChildrenDrafts(): ActiveQuery
    {
        return $this->hasMany(BachelorApplication::class, ['parent_draft_id' => 'id']);
    }

    


    public static function getBlockStatusAliasList(): array
    {
        return [
            BachelorApplication::BLOCK_STATUS_ENABLED => Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Название статуса блокировки "Заблокировано"; формы "Заявления": `Заблокировано`'
            ),
            BachelorApplication::BLOCK_STATUS_DISABLED => Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Название статуса блокировки "Разблокировано"; формы "Заявления": `Разблокировано`'
            ),
        ];
    }

    






    public static function getApplicationArchiveTree(BachelorApplication $parentApp, int $id_application_came_from, Controller $controller): array
    {
        $tnBachelorApplication = BachelorApplication::tableName();
        $childrenList = BachelorApplication::find()
            ->with(['childrenDrafts.childrenDrafts.childrenDrafts.childrenDrafts.childrenDrafts.childrenDrafts'])
            ->andWhere(["{$tnBachelorApplication}.parent_draft_id" => $parentApp->id])
            ->orderBy(["{$tnBachelorApplication}.created_at" => SORT_ASC])
            ->all();
        if (!$childrenList) {
            return [];
        }

        return BachelorApplication::getApplicationArchiveNode($childrenList, $id_application_came_from, $controller);
    }

    






    public static function getApplicationArchiveNode(array $applications, int $id_application_came_from, Controller $controller): array
    {
        $result = [];
        foreach ($applications as $application) {
            

            $result[] = [
                'checkable' => false,
                'selectable' => false,
                'text' => $controller->renderPartial(
                    'partial/_view-archive-panel',
                    [
                        'application' => $application,
                        'id_application_came_from' => $id_application_came_from,
                    ]
                ),
                'nodes' => BachelorApplication::getApplicationArchiveTree($application, $id_application_came_from, $controller),
            ];
        }

        return $result;
    }

    


    public function hasSentApps(): bool
    {
        $tnBachelorApplication = BachelorApplication::tableName();
        return $this->user->hasSentAppsQuery()
            ->andWhere(["{$tnBachelorApplication}.type_id" => $this->type_id])
            ->exists();
    }

    


    public function hasAnyOtherApps(): bool
    {
        $tnBachelorApplication = BachelorApplication::tableName();
        return $this->user->getRawApplications()
            ->andWhere(["{$tnBachelorApplication}.type_id" => $this->type_id])
            ->andWhere(['not', ["{$tnBachelorApplication}.id" => $this->id]])
            ->andWhere(['not', ["{$tnBachelorApplication}.status" => BachelorApplication::STATUS_CREATED]])
            ->exists();
    }

    


    public function canCreateDraft(): bool
    {
        return !($this->type->disable_creating_draft_if_exist_sent_application && $this->hasSentApps());
    }

    


    public function isFirstAttemptSendApp(): bool
    {
        return !$this->hasAnyOtherApps();
    }

    public function getAttachedFilesInfo(): array
    {
        return [...$this->getAdditionalAttachmentsInfo(), ...$this->getRegulationsAttachmentsInfo()];
    }

    public function attachFile(IReceivedFile $receivingFile, DocumentType $documentType): ?File
    {
        $attachmentTypeIds = AttachmentType::find()
            ->joinWith(['documentType document_type'])
            ->andWhere([
                'document_type.ref_key' => $documentType->ref_key,
                'attachment_type.hidden' => false,
            ])
            ->select(['attachment_type.id'])
            ->column();
        if (!$attachmentTypeIds) {
            return null;
        }
        $file = null;
        $file = $this->abiturientQuestionary->questionaryFileAttacher->attachFileToQuestionaryAttachments($receivingFile, $attachmentTypeIds, $file);
        $file = $this->applicationFileAttacher->attachFileToApplicationAttachments($receivingFile, $attachmentTypeIds, $file);
        $file = $this->abiturientQuestionary->questionaryFileAttacher->attachFileToQuestionaryRegulations($receivingFile, $attachmentTypeIds, $file);
        $file = $this->applicationFileAttacher->attachFileToApplicationRegulations($receivingFile, $attachmentTypeIds, $file);
        return $this->abiturientQuestionary->questionaryFileAttacher->attachFileToUserRegulations($receivingFile, $attachmentTypeIds, $file);
    }

    public function removeNotPassedFiles(array $file_ids_to_ignore)
    {
        $base_query = Attachment::find()
            ->joinWith('attachmentType')
            ->andWhere([
                'attachment_type.system_type' => AttachmentType::SYSTEM_TYPE_COMMON,
                'attachment_type.hidden' => false,
                'attachment.application_id' => $this->id,
                'attachment.deleted' => false,
            ])
            ->joinWith(['linkedFile linked_file']);
        $ignored_application_attachment_ids = (clone $base_query)
            ->select(['MAX(attachment.id) id'])
            ->andWhere(['linked_file.id' => $file_ids_to_ignore])
            ->groupBy(['linked_file.id', 'attachment_type.id']);

        $attachments_to_delete = $base_query
            ->andWhere(['not', ['attachment.id' => $ignored_application_attachment_ids]])
            ->all();

        $questionary = $this->abiturientQuestionary;
        $ignored_questionary_attachment_ids = Attachment::find()
            ->select(['MAX(attachment.id) id'])
            ->joinWith('attachmentType')
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['attachment.id' => (new Query())->from(['a' => $questionary->getAttachments()])->select('a.id')])
            ->andWhere(['linked_file.id' => $file_ids_to_ignore])
            ->groupBy(['linked_file.id', 'attachment_type.id']);

        $questionary_attachments = $questionary->getAttachments()
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['NOT', ['attachment.id' => $ignored_questionary_attachment_ids]])
            ->all();
        $attachments_to_delete = [...$attachments_to_delete, ...$questionary_attachments];

        
        foreach ($attachments_to_delete as $attachment_to_delete) {
            $attachment_to_delete->silenceSafeDelete();
        }
    }

    




    public function getHasPassedApplication(): bool
    {
        return $this->hasPassedApplication();
    }

    




    public function hasPassedApplication(): bool
    {
        return $this->hasApprovedApplicationQuery()->exists();
    }

    








    public function hasPassedApplicationWithEditableAttachments($relatedEntity = ''): bool
    {
        if ($this->status != BachelorApplication::STATUS_CREATED) {
            return false;
        }

        $tnAttachmentType = AttachmentType::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();

        $attachmentQurtyArray = [
            'and',
            ["{$tnAttachmentType}.hidden" => false],
            ["{$tnAttachmentType}.related_entity" => $relatedEntity],
            [
                'or',
                ["{$tnAttachmentType}.allow_delete_file_after_app_approve" => true],
                ["{$tnAttachmentType}.allow_add_new_file_after_app_approve" => true],
            ],
        ];

        
        
        
        $rawQuery1 = $this->hasApprovedApplicationQuery()
            ->select(['id' => "{$tnBachelorApplication}.id"])
            ->joinWith('type.campaign.attachmentTypes')
            ->andWhere($attachmentQurtyArray);

        
        
        
        $rawQuery2 = AttachmentType::find()
            ->select(['id' => "{$tnAttachmentType}.id"])
            ->andWhere($attachmentQurtyArray)
            ->andWhere([
                'IS NOT',
                $this->hasApprovedApplicationQuery()
                    ->select('id')
                    ->limit(1),
                null
            ])
            ->andWhere(["{$tnAttachmentType}.admission_campaign_ref_id" => null]);

        return $rawQuery1
            ->union($rawQuery2)
            ->exists();
    }

    public function getApplicationReturnAttachments(): ActiveQuery
    {
        return $this->getAttachments()
            ->joinWith('attachmentType attachment_type_table', false)
            ->andOnCondition([
                Attachment::tableName() . '.deleted' => false,
                'attachment_type_table.system_type' => AttachmentType::SYSTEM_TYPE_APPLICATION_RETURN
            ]);
    }

    public function getApplicationReturnAttachmentCollection(): ApplicationAttachmentCollection
    {
        return new ApplicationAttachmentCollection(
            $this->getApplicationReturnAttachmentType(),
            $this,
            $this->applicationReturnAttachments
        );
    }

    protected function getExcludedFromEncodingProps(): array
    {
        return [
            'moderator_comment',
        ];
    }

    public function getApplicationReturnAttachmentType(): AttachmentType
    {
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_APPLICATION_RETURN);
    }

    





    public function hasApprovedApplicationQuery(): ActiveQuery
    {
        $tn = BachelorApplication::tableName();
        return DraftsManager::getApplicationDraftQuery($this->user, $this->type, IDraftable::DRAFT_STATUS_APPROVED)
            ->andWhere(["{$tn}.have_order" => true]);
    }

    public function hasApprovedApplication(): bool
    {
        return $this->hasApprovedApplicationQuery()->exists();
    }

    public function getApplyText(): string
    {
        return Yii::t(
            'abiturient/header/all',
            'Подпись ссылки подачи заявления на панели навигации ЛК: `Отправить в приемную комиссию`'
        );
    }

    


    public function getFormattedModeratorComment(): ?string
    {
        return CommentNavigationLinkerWidget::renderFormattedModeratorComment($this->moderator_comment, $this->id);
    }

    public function notifyAboutSendApplicationToCommission(bool $is_first_attempt)
    {
        $title = Yii::$app->configurationManager->getText('new_application_apply_notification_title', $this->type ?? null);
        $message = Yii::$app->configurationManager->getText('new_application_apply_notification_body', $this->type ?? null);
        $variables = [
            '{campaignName}' => $this->type->getCampaignName(),
            '{applicantName}' => $this->user->getAbsFullName(),
            '{applicantEmail}' => $this->user->email,
        ];
        $title = strtr($title, $variables);
        $message = strtr($message, $variables);

        $managers = $this->type->campaignModerators;
        foreach ($managers as $manager) {
            $manager_notifications_configurator = ManagerNotificationsConfigurator::getInstance($manager);
            if (($manager_notifications_configurator->notify_about_first_application_apply && $is_first_attempt) || $manager_notifications_configurator->notify_about_any_application_apply) {
                $deliverers = $manager_notifications_configurator->getDeliverers();
                foreach ($deliverers as $deliverer) {
                    [$status, $message] = $deliverer->deliverMessage($title, $message);
                    if (!$status && $message) {
                        Yii::error($message, 'notifyAboutSendApplicationToCommission');
                    }
                }
            }
        }
    }

    public function getHasVerifiedAgreements(): bool
    {
        return $this->getSpecialities()
            ->joinWith('agreement', false)
            ->andWhere([
                AdmissionAgreement::tableName() . '.status' => AdmissionAgreement::STATUS_VERIFIED,
                AdmissionAgreement::tableName() . '.archive' => false
            ])->exists();
    }

    public function getNotFilledRequiredEducationScanTypeIds(): array
    {
        return Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getEduAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds(AttachmentType::RELATED_ENTITY_EDUCATION, ArrayHelper::getValue($this, 'type.campaign.referenceType.reference_uid'))
        );
    }

    public function getNotFilledRequiredBenefitsScanTypeIds(): array
    {
        return Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getBenefitsAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds([
                AttachmentType::RELATED_ENTITY_OLYMPIAD,
                AttachmentType::RELATED_ENTITY_PREFERENCE,
                AttachmentType::RELATED_ENTITY_TARGET_RECEPTION
            ], ArrayHelper::getValue($this, 'type.campaign.referenceType.reference_uid'))
        );
    }

    public function getNotFilledRequiredSpecialitiesScanTypeIds(): array
    {
        return Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds(AttachmentType::RELATED_ENTITY_APPLICATION, ArrayHelper::getValue($this, 'type.campaign.referenceType.reference_uid'))
        );
    }

    public function getNotFilledRequiredExamsScanTypeIds(): array
    {
        return Attachment::getNotFilledRequiredAttachmentTypeIds(
            $this->getEgeAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds(AttachmentType::RELATED_ENTITY_EGE, ArrayHelper::getValue($this, 'type.campaign.referenceType.reference_uid'))
        );
    }

    public function getBachelorSpecialityToRejectEnrollment(array $bachelor_specialities): ?BachelorSpeciality
    {
        foreach ($bachelor_specialities as $bachelor_spec) {
            if ($bachelor_spec->is_enlisted && !empty($bachelor_spec->enrollmentRejectionAttachments)) {
                return $bachelor_spec;
            }
        }

        return null;
    }
}
