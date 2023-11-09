<?php

namespace common\modules\abiturient\controllers;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\ArrayToXmlConverter\ArrayToXmlConverter;
use common\components\EntrantTestManager\EntrantTestManager;
use common\components\ErrorMessageAnalyzer;
use common\components\ManagerInforming\ManagerInforming;
use common\components\RegulationRelationManager;
use common\models\Attachment;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\repositories\UserRegulationRepository;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\CheckAllApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\NeedBlockAndUpdateProcessor;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\QuestionarySearch;
use common\modules\abiturient\models\repositories\SpecialityRepository;
use common\services\abiturientController\bachelor\accounting_benefits\BenefitsService;
use common\services\abiturientController\bachelor\accounting_benefits\OlympiadsService;
use common\services\abiturientController\bachelor\accounting_benefits\TargetReceptionsService;
use common\services\abiturientController\bachelor\bachelorSpeciality\BachelorSpecialityService;
use common\services\abiturientController\bachelor\bachelorSpeciality\SpecialityPrioritiesService;
use common\services\abiturientController\bachelor\ContractorService;
use common\services\abiturientController\bachelor\EducationService;
use common\services\abiturientController\questionary\ParentDataService;
use common\services\abiturientController\questionary\PassportDataService;
use common\services\abiturientController\sandbox\AllApplicationAttachmentsService;
use common\services\abiturientController\sandbox\BindApplicationService;
use common\services\abiturientController\sandbox\DeclineApplicationService;
use common\services\abiturientController\sandbox\PartialApplicationSavingService;
use common\services\abiturientController\sandbox\SandboxApplicationsTableService;
use common\services\abiturientController\sandbox\SandboxModerateService;
use common\services\abiturientController\sandbox\ViewApplicationService;
use Throwable;
use Yii;
use yii\base\Action;
use yii\base\Model;
use yii\base\Module;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;




class SandboxController extends Controller
{
    
    private BenefitsService $benefitsService;

    
    private OlympiadsService $olympiadsService;

    
    private TargetReceptionsService $targetReceptionsService;

    
    private ContractorService $contractorService;

    
    private BachelorSpecialityService $bachelorSpecialityService;

    
    private SpecialityPrioritiesService $specialityPrioritiesService;

    
    private SandboxApplicationsTableService $sandboxApplicationsTableService;

    
    private ParentDataService $parentDataService;

    
    private EducationService $educationService;

    
    private PassportDataService $passportDataService;

    
    private SandboxModerateService $sandboxModerateService;

    
    private PartialApplicationSavingService $partialApplicationSavingService;

    
    private DeclineApplicationService $declineApplicationService;

    
    private ViewApplicationService $viewApplicationService;

    
    private AllApplicationAttachmentsService $allApplicationAttachmentsService;

    
    private BindApplicationService $bindApplicationService;

    




















    public function __construct(
        $id,
        $module,
        BenefitsService $benefitsService,
        EducationService $educationService,
        OlympiadsService $olympiadsService,
        ContractorService $contractorService,
        ParentDataService $parentDataService,
        PassportDataService $passportDataService,
        BindApplicationService $bindApplicationService,
        SandboxModerateService $sandboxModerateService,
        ViewApplicationService $viewApplicationService,
        TargetReceptionsService $targetReceptionsService,
        BachelorSpecialityService $bachelorSpecialityService,
        SpecialityPrioritiesService $specialityPrioritiesService,
        DeclineApplicationService $declineApplicationService,
        PartialApplicationSavingService $partialApplicationSavingService,
        SandboxApplicationsTableService $sandboxApplicationsTableService,
        AllApplicationAttachmentsService $allApplicationAttachmentsService,
        $config = []
    ) {
        $this->benefitsService = $benefitsService;
        $this->educationService = $educationService;
        $this->olympiadsService = $olympiadsService;
        $this->contractorService = $contractorService;
        $this->parentDataService = $parentDataService;
        $this->passportDataService = $passportDataService;
        $this->bindApplicationService = $bindApplicationService;
        $this->sandboxModerateService = $sandboxModerateService;
        $this->viewApplicationService = $viewApplicationService;
        $this->targetReceptionsService = $targetReceptionsService;
        $this->bachelorSpecialityService = $bachelorSpecialityService;
        $this->specialityPrioritiesService = $specialityPrioritiesService;
        $this->declineApplicationService = $declineApplicationService;
        $this->partialApplicationSavingService = $partialApplicationSavingService;
        $this->sandboxApplicationsTableService = $sandboxApplicationsTableService;
        $this->allApplicationAttachmentsService = $allApplicationAttachmentsService;

        parent::__construct($id, $module, $config);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@common/modules/abiturient/views/sandbox');
    }

    





    public function beforeAction($action)
    {
        $allowMasterSystemManager = Yii::$app->configurationManager->getMasterSystemManagerSetting('use_master_system_manager_interface');
        if ($allowMasterSystemManager && $action->id !== 'informing') {
            $this->redirect(['sandbox/informing', 'name' => 'system_manager.manager_is_not_allowed']);
            return true;
        }

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'controllers' => ['sandbox'],
                        'allow' => false,
                        'roles' => [User::ROLE_ADMINISTRATOR],
                    ],
                    [
                        'actions' => [
                            'bind-parent',
                            'bind',
                            'decline',
                            'delete-parent-data',
                            'delete-passport',
                            'full-package-xml',
                            'informing',
                            'is-blocked',
                            'moderate',
                            'parent-form',
                            'render-docs',
                            'reset-filters', 
                            'return-to-moderate',
                            'save-address-data',
                            'save-exam-results',
                            'save-main-data',
                            'set-parent-data',
                            'set-passport',
                            'unblock',
                            'update-questionary',
                            'validate-application',
                            'save-application',
                            'want-delete',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ],
                    [
                        'actions' => [
                            'all',
                            'approved',
                            'declined',
                            'deleted',
                            'enlisted',
                            'get-all-attachments',
                            'index',
                            'preparing',
                            'questionaries',
                            'view-archive-application',
                            'view-questionary',
                            'view',
                            'want-delete',
                            'enrollment-rejection',
                        ],
                        'allow' => true,
                        'roles' => [
                            User::ROLE_VIEWER,
                            User::ROLE_MANAGER
                        ]
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return ['error' => ['class' => \yii\web\ErrorAction::class]];
    }

    public function actionUpdateQuestionary(int $id, int $questionary_id)
    {
        $questionary = AbiturientQuestionary::findOne((int)$questionary_id);
        if ($questionary) {
            $questionary->getFrom1CWithParents();
        }

        return $this->redirect(Url::to(['sandbox/moderate', 'id' => $id]), 302);
    }

    public function actionIndex($old = null, $block = null)
    {
        $user = Yii::$app->user->identity;

        if ($old == '1') {
            Yii::$app->session->setFlash('alert', [
                'body' => 'Открытое вами заявление устарело и было актуализировано из ПК',
                'options' => ['class' => 'alert-info']
            ]);
        }
        if ($block == '1') {
            Yii::$app->session->setFlash('alert', [
                'body' => 'Заявление, которое вы пытались открыть, уже проверяется другим модератором',
                'options' => ['class' => 'alert-danger']
            ]);
        }

        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'moderate')
        );
    }

    public function actionAll()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'all')
        );
    }

    public function actionApproved()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'approved')
        );
    }

    public function actionEnlisted()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'enlisted')
        );
    }

    


    public function actionDeleted()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'deleted')
        );
    }

    


    public function actionWantDelete()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'want-delete')
        );
    }

    


    public function actionPreparing()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'preparing')
        );
    }

    public function actionDeclined()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'declined')
        );
    }

    public function actionQuestionaries()
    {
        $searchModel = new QuestionarySearch();
        $questionariesDataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render("questionaries", [
            'questionaries' => $questionariesDataProvider,
            'searchModel' => $searchModel,
            'type' => 'questionaries'
        ]);
    }

    




    public function actionResetFilters(string $type)
    {
        Yii::$app->session->remove('moderate_filters');
        return $this->redirect(['sandbox/' . $type]);
    }

    public function actionModerate(int $id)
    {
        $user = Yii::$app->user->identity;

        $request = Yii::$app->request;
        $validation_errors = [];
        $passportErrors = [];

        
        $application = $this->sandboxModerateService->getApplicationById($id);
        if ($application->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('sandbox/errors', "Сообщение модератору при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления {fio}, необходимо открыть его повторно`", ['fio' => $application->fio]),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/sandbox/index']);
        }

        if ($moderatingAppId = $this->sandboxModerateService->checkDraftStatusToModerate($application)) {
            return $this->redirect(['/sandbox/moderate', 'id' => $moderatingAppId]);
        }
        if (!$application->moderationAllowedByStatus()) {
            return $this->redirect('/sandbox/index', 302);
        }

        $questionary = $application->abiturientQuestionary;

        [$blocked, $_] = $application->isApplicationBlocked();
        if ($blocked) {
            return $this->redirect(Url::toRoute(['/sandbox/index', 'block' => 1]), 302);
        }

        $this->sandboxModerateService->startModeratingProcess($user, $application);

        $hasChangesIn1CWithNewerDate = false;
        $hasBlockedBy1C = false;
        if (!in_array($application->status, [BachelorApplication::STATUS_WANTS_TO_RETURN_ALL, BachelorApplication::STATUS_WANTS_TO_BE_REMOTE])) {
            [$hasChangesIn1CWithNewerDate, $hasBlockedBy1C] = NeedBlockAndUpdateProcessor::getProcessedNeedBlockAndUpdate($application);
        }
        $add_errors_json = Yii::$app->session->get('add_errors');
        $add_errors = json_decode((string)$add_errors_json);
        Yii::$app->session->remove('add_errors');

        
        $specialities = $this->bachelorSpecialityService->getSelectedSpecialityList($application);

        $canEdit = $application->type->moderator_allowed_to_edit && $questionary->canEditQuestionary() && $application->canEdit();

        $haveValidationErrors = false;
        $forbiddenResultExists = $application->getEgeResults()
            ->andWhere(['cget_exam_form_id' => null])
            ->exists();

        [
            'egeResults' => $egeResults,
            'validationErrors' => $validationEgeErrors,
            'haveValidationEgeErrors' => $haveValidationEgeErrors,
        ] = $this->sandboxModerateService->getValidatedEgeResults($application);
        $validation_errors = array_merge($validation_errors, $validationEgeErrors);

        $pending_contractors = $this->contractorService->checkAllPendingContractors($application);
        $need_approve_contractor = $this->contractorService->hasAtLeastOnePendingContractor($pending_contractors);

        
        if ($request->isPost && $specialities && !$hasChangesIn1CWithNewerDate && !$hasBlockedBy1C && !$need_approve_contractor) {
            $application->load(Yii::$app->request->post());
            $personal_data = $questionary->personalData;
            $address_data = $questionary->addressData;
            $actualAddressData = $questionary->getOrCreateActualAddressData();
            $passports = $questionary->passportData;

            $passportErrors = $this->sandboxModerateService->validatePassports($passports);
            [
                'checkErrors' => $check_errors,
                'haveValidationErrors' => $haveValidationErrors,
            ] = $this->sandboxModerateService->checkBalls($application, $specialities);
            if ($check_errors) {
                Yii::$app->session->setFlash('checkEgeErrorsAbit', json_encode($check_errors));
            }

            $validateAgreementDate = $this->sandboxModerateService->validateAgreement($application, $specialities);

            if (
                $application->validate() &&
                $personal_data->validate() &&
                empty($passportErrors) &&
                $address_data->validate() &&
                $actualAddressData->validate() &&
                Model::validateMultiple($specialities) &&
                $validateAgreementDate &&
                $application->validateUnstagedDisciplineSets() &&
                $application->validateUnstagedDisciplineResults() &&
                !$haveValidationErrors &&
                !$forbiddenResultExists &&
                !$haveValidationEgeErrors
            ) {
                $approval_error = null;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $application->save(true, ['moderator_comment']); 
                    gc_disable(); 
                    if ($application->getSandboxSendHandler()->send()) {
                        $this->sandboxModerateService->afterSuccessFullApplicationApprovement($user, $application);
                        $transaction->commit();

                        return $this->redirect('/sandbox/index');
                    }

                    $application = $this->sandboxModerateService->afterFailureOnApplicationApprovement($application);
                    $transaction->commit();
                } catch (Throwable $e) {
                    $transaction->rollBack();
                    $approval_error = $e->getMessage();
                }

                $error_text = $this->sandboxModerateService->composeApplyingStepsErrors($application, $approval_error);
                Yii::$app->session->setFlash('appApprovingError', $error_text);

                if (!$approval_error && $this->failedPossibleOfDuplicate($application->applyingSteps)) {
                    [
                        'abiturientDoubles' => $abiturientDoubles,
                        'doublesForParents' => $doublesForParents,
                    ] = $this->sandboxModerateService->checkPersonDuplicates($questionary);

                    if ($abiturientDoubles || $doublesForParents) {
                        Yii::$app->session->setFlash('abiturientDoubles', $abiturientDoubles);
                        Yii::$app->session->setFlash('doublesForParents', $doublesForParents);

                        return $this->redirect(Url::to([
                            'sandbox/view',
                            'id' => $application->id,
                        ]));
                    }
                }

                return $this->redirect(['/sandbox/moderate', 'id' => $application->id]);
            }

            $modelsToValidate = [
                $application,
                $personal_data,
                $address_data,
                $actualAddressData,
            ];
            foreach ($modelsToValidate as $model) {
                if ($model && $model->errors) {
                    $validation_errors[] = $model->errors;
                }
            }
        }

        if ($forbiddenResultExists) {
            $validation_errors[] = [[Yii::t(
                'sandbox/moderate/all',
                'Текст ошибки об отсутствии формы сдачи ВИ, на странице проверки заявления: `У поступающего присутствуют результаты вступительных испытаний с без выбранной формы сдачи. Отклоните заявление для выбора формы поступающим или выберите форму самостоятельно.`'
            )]];
        }

        if (!$application->validateUnstagedDisciplineSets()) {
            $validation_errors[] = [[Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки для отсутствующего набора ВИ; при валидации данных анкеты или заявления: `Необходимо подтвердить набор вступительных испытаний`'
            )]];
        }
        if (!$application->validateUnstagedDisciplineResults()) {
            $validation_errors[] = [[Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при не сохранённых результатах ВИ; при валидации данных анкеты или заявления: `Необходимо сохранить результаты вступительных испытаний`'
            )]];
        }
        if ($haveValidationErrors) {
            $validation_errors[] = [[Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки валидации результатов ВИ; при валидации данных анкеты или заявления: `Ошибка валидации результатов вступительных испытаний`'
            )]];
        }
        if ($passportErrors) {
            $validation_errors = ArrayHelper::merge($validation_errors, $passportErrors);
        }
        $specErrors = $this->sandboxModerateService->validateSpecialities($specialities);

        $allowBenefitCategories = !ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
        $available_specialities = SpecialityRepository::getCurrentAvailableSpecialities(
            $application,
            $allowBenefitCategories
        )
            ->with('educationFormRef')
            ->with('educationFormRef')
            ->with('educationSourceRef')
            ->with('competitiveGroupRef')
            ->with('detailGroupRef')
            ->with('subdivisionRef')
            ->all();

        $individualAchievements = new ActiveDataProvider([
            'query' => $application->getIndividualAchievements()
        ]);

        $regulations = UserRegulationRepository::GetAllUserRegulationsByRelatedEntity(
            $application->abiturientQuestionary,
            array_keys(RegulationRelationManager::GetRelatedList()),
            $application
        );

        foreach ($regulations as $regulation) {
            if ($regulation->regulation->attachment_type !== null && $regulation->getAttachments()->exists()) {
                $newAttachment = new Attachment();
                $newAttachment->owner_id = $application->user_id;
                $newAttachment->attachment_type_id = $regulation->regulation->attachment_type;
                $regulation->setRawAttachment($newAttachment);
            }
        }
        [
            'reallySentApplication' => $really_sent_app,
            'reallySentQuestionary' => $really_sent_questionary,
        ] = $this->sandboxModerateService->getReallySentApplicationAndQuestionary($application);

        $resultTargets = $this->targetReceptionsService->getTargets($application->id);
        $resultBenefits = $this->benefitsService->getBenefits($application->id);
        $resultOlympiads = $this->olympiadsService->getOlympiads($application->id);

        $actual_application = DraftsManager::getActualApplication($application->user, $application->type);
        $actual_questionary = DraftsManager::getActualQuestionary($application->user);

        $application->setScenario(BachelorApplication::SCENARIO_APPLICATION_WITH_EDUCATION);
        return $this->render(
            'moderate',
            ArrayHelper::merge(
                [
                    'application_comparison_with_actual' => $actual_application &&
                        $actual_application->id != $application->id ?
                        EntitiesComparator::compare($actual_application, $application) :
                        null,
                    'questionary_comparison_with_actual' => $actual_questionary &&
                        $actual_questionary->id != $questionary->id ?
                        EntitiesComparator::compare($actual_questionary, $questionary) :
                        null,
                    'application_comparison_with_sent' => $really_sent_app &&
                        $really_sent_app->id != $application->id ?
                        EntitiesComparator::compare($really_sent_app, $application) :
                        null,
                    'questionary_comparison_with_sent' => $really_sent_questionary &&
                        $really_sent_questionary->id != $questionary->id ?
                        EntitiesComparator::compare($really_sent_questionary, $questionary) :
                        null,
                    'passports' => new ActiveDataProvider([
                        'query' => $questionary->getPassportData()
                    ]),
                    'egeResult' => new EgeResult(),
                    'competitiveGroupEntranceTest' => DictionaryCompetitiveGroupEntranceTest::getDataProviderByApplication($application),
                    'parents' => new ActiveDataProvider([
                        'query' => $questionary->getParentData()
                    ]),
                    'canEdit' => $canEdit,
                    'application' => $application,
                    'questionary' => $questionary,
                    'abitAvatar' => $questionary->getComputedAbiturientAvatar(),
                    'regulations' => $regulations,
                    'available_specialities' => $available_specialities,
                    'individualAchievements' => $individualAchievements,
                    'add_errors' => $add_errors,
                    'validation_errors' => $validation_errors,
                    'target_receptions' => ArrayHelper::map($application->bachelorTargetReceptions, 'id', 'name'),
                    'specialityErrors' => $specErrors,
                    'egeResults' => $egeResults,
                    'specialities' => $specialities,
                    'resultOlympiads' => $resultOlympiads,
                    'resultBenefits' => $resultBenefits,
                    'resultTargets' => $resultTargets,
                    'hasChangesIn1CWithNewerDate' => $hasChangesIn1CWithNewerDate,
                    'hasBlockedBy1C' => $hasBlockedBy1C,
                    'pending_contractors' => $pending_contractors,
                    'need_approve_contractor' => $need_approve_contractor,
                    'targetReceptionsService' => $this->targetReceptionsService,
                    'olympiadsService' => $this->olympiadsService,
                    'benefitsService' => $this->benefitsService,
                    'bachelorSpecialityService' => $this->bachelorSpecialityService,
                    'specialityPrioritiesService' => $this->specialityPrioritiesService,
                ],
                SpecialityRepository::getSpecialityFiltersData($application)
            )
        );
    }

    





    private function failedPossibleOfDuplicate(array $steps): bool
    {
        return (bool)array_filter($steps, function (ApplicationApplyingStep $step) {
            return $step->errors && array_filter($step->errors, function (string $error) {
                return ErrorMessageAnalyzer::isUserRefDuplicateError($error);
            });
        });
    }

    public function actionSaveMainData(int $quest_id)
    {
        $questionary = $this->partialApplicationSavingService->getAbiturientQuestionaryById($quest_id);
        $errorEmailValidate = $this->partialApplicationSavingService->validateEmail($questionary);
        if ($errorEmailValidate) {
            return $this->asJson([
                'status' => false,
                'message' => $errorEmailValidate,
            ]);
        }

        $errorPersonalDataValidate = $this->partialApplicationSavingService->validatePersonalData($questionary);
        if ($errorPersonalDataValidate) {
            return $this->asJson([
                'status' => false,
                'message' => $errorPersonalDataValidate
            ]);
        }

        return $this->asJson([
            'status' => true,
            'message' => Yii::t(
                'sandbox/moderate/all',
                'Текст сообщения при успешном сохранении формы персональных данных: `Данные сохранены успешно`'
            ),
        ]);
    }

    public function actionSaveAddressData(string $type, int $questionary_id)
    {
        $questionary = $this->partialApplicationSavingService->getAbiturientQuestionaryById($questionary_id);
        $errorAddressDataValidate = $this->partialApplicationSavingService->validateAddressData(
            $questionary,
            $type
        );
        if ($errorAddressDataValidate) {
            return $this->asJson([
                'status' => false,
                'message' => $errorAddressDataValidate
            ]);
        }

        return $this->asJson([
            'status' => true,
            'message' => Yii::t(
                'sandbox/moderate/all',
                'Текст сообщения при успешном сохранении формы адреса: `Данные сохранены успешно`'
            ),
        ]);
    }

    public function actionDecline(int $id)
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect('/sandbox/index', 302);
        }

        $application = BachelorApplication::findOne((int)$id);
        if ($application->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('sandbox/errors', "Сообщение модератору при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления {fio}, необходимо открыть его повторно`", ['fio' => $application->fio]),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/sandbox/index']);
        }
        if ($application->block_status != BachelorApplication::BLOCK_STATUS_ENABLED) {
            return $this->redirect(Url::to(['sandbox/moderate', 'id' => $application->id]), 302);
        }
        $sent_app = DraftsManager::getApplicationDraft($application->user, $application->type, IDraftable::DRAFT_STATUS_SENT);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $declined_app = $this->declineApplicationService->decline(Yii::$app->user->identity, $sent_app);
            Yii::$app->notifier->notifyAboutDeclineApplication($declined_app->user_id, $declined_app->moderator_comment);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect('/sandbox/index', 302);
    }

    



    public function actionSaveExamResults(int $id)
    {
        
        $application = BachelorApplication::findOne($id);
        if (empty($application)) {
            return $this->asJson([
                'status' => false,
                'message' => 'Не удалось найти заявление.',
            ]);
        }
        $msgs = [];
        [
            'hasError' =>  $hasError,
            'hasChanges' => $hasChanges,
        ] = EntrantTestManager::proceedEntrantTestFromPost(Yii::$app->request, $application, $msgs);
        return $this->asJson([
            'status' => !$hasError,
            'message' => implode(', ', $msgs),
        ]);
    }

    



    public function actionSaveApplication(int $id)
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/sandbox/moderate', 'id' => $id]);
        }

        $application = BachelorApplication::findOne($id);
        if (empty($application)) {
            return $this->asJson([
                'status' => false,
                'message' => 'Не удалось найти заявление.',
            ]);
        }
        $application->setScenario(BachelorApplication::SCENARIO_APPLICATION_WITH_EDUCATION);
        $questionary = $application->abiturientQuestionary;
        $canEdit = $application->type->moderator_allowed_to_edit && $questionary->canEditQuestionary() && $application->canEdit();
        if (!$canEdit) {
            return $this->redirect(['/sandbox/moderate', 'id' => $id]);
        }

        
        $specialities = $this->bachelorSpecialityService->getSelectedSpecialityList($application);
        [$spec_changed, $_] = $this->bachelorSpecialityService->processLoadedData(
            $application,
            $specialities,
            Yii::$app->request->post(),
            true
        );

        if (!$spec_changed) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                'options' => ['class' => 'alert-warning']
            ]);
        }

        return $this->redirect(['/sandbox/moderate', 'id' => $id]);
    }

    public function actionUnblock(int $id)
    {
        $application = BachelorApplication::findOne((int)$id);
        if ($application != null) {
            $application->fullyUnblockApplication();
        }
        return $this->redirect('/sandbox/index', 302);
    }

    public function actionViewQuestionary(int $id)
    {
        $questionary = AbiturientQuestionary::findOne((int)$id);

        return $this->render("questionary_view", [
            'questionary' => $questionary,
        ]);
    }

    public function actionView(int $id)
    {
        $relationInfo = [];
        $code_message = '';
        $parents_code_message = '';
        if ($abiturientDoubles = Yii::$app->session->getFlash('abiturientDoubles')) {
            $code_message = Yii::t(
                'sandbox/errors',
                'Текст при сопоставлении поступающего: `Обнаружено совпадение персональных данных поступающего с данными физического лица в базе ПК. Следует ли сопоставить этого поступающего с существующим физическим лицом?`'
            );
            $relationInfo['abit'] = $abiturientDoubles;
        }
        if ($doublesForParents = Yii::$app->session->getFlash('doublesForParents')) {
            $parents_code_message = Yii::t(
                'sandbox/errors',
                'Текст при сопоставлении родителя поступающего: `Обнаружено совпадение персональных данных родителя с данными физического лица в базе ПК. Следует ли сопоставить родителя с существующим физическим лицом?`'
            );
            $relationInfo['parents'] = $doublesForParents;
        }

        [
            'application' => $application,
            'questionary' => $questionary,
            'regulations' => $regulations,
            'moderatingAppId' => $moderatingAppId,
            'individualAchievements' => $individualAchievements,
        ] = $this->viewApplicationService->getAllModelForView($id);

        $resultTargets = $this->targetReceptionsService->getTargets($id);
        $resultBenefits = $this->benefitsService->getBenefits($id);
        $resultOlympiads = $this->olympiadsService->getOlympiads($id);

        return $this->render(
            'view',
            [
                'id' => $id,
                'individualAchievements' => $individualAchievements,
                'competitiveGroupEntranceTest' => DictionaryCompetitiveGroupEntranceTest::getDataProviderByApplication($application),
                'questionary' => $questionary,
                'regulations' => $regulations,
                'application' => $application,
                'relationInfo' => $relationInfo,
                'code_message' => $code_message,
                'parents_code_message' => $parents_code_message,
                'moderate_app_id' => $moderatingAppId,
                'resultOlympiads' => $resultOlympiads,
                'resultBenefits' => $resultBenefits,
                'resultTargets' => $resultTargets,
                'benefitsService' => $this->benefitsService,
                'targetReceptionsService' => $this->targetReceptionsService,
                'olympiadsService' => $this->olympiadsService,
            ]
        );
    }

    public function actionGetAllAttachments(int $id, string $type)
    {
        [
            'hasError' => $hasError,
            'filename' => $filename,
            'pathToZipArchive' => $pathToZipArchive,
        ] = $this->allApplicationAttachmentsService->getZipArchiveAttribute($id, $type);

        if ($hasError) {
            return false;
        }

        Yii::$app->response->sendFile($pathToZipArchive, $filename)->on(
            Response::EVENT_AFTER_SEND,
            function ($event) {
                unlink($event->data);
            },
            $pathToZipArchive
        );
    }

    public function actionBind()
    {
        $id = Yii::$app->request->post('application_id');
        $url = Url::to(['sandbox/moderate', 'id' => $id]);
        if (!Yii::$app->request->isPost) {
            return $this->redirect($url, 302);
        }

        if ($this->bindApplicationService->bindUser(Yii::$app->user->identity, $id)) {
            Yii::$app->session->setFlash('bind', 'Поступающий успешно сопоставлен с Физ. лицом. Можно "Одобрить" заявление.');
        }

        return $this->redirect($url, 302);
    }

    public function actionBindParent(int $id)
    {
        $url = Url::to(['sandbox/moderate', 'id' => $id]);
        if (!Yii::$app->request->isPost) {
            return $this->redirect($url);
        }

        if ($parentFullName = $this->bindApplicationService->bindParent()) {
            Yii::$app->session->setFlash('bind', "Родитель {$parentFullName} успешно сопоставлен с Физ. лицом. Можно \"Одобрить\" заявление.");
        }

        return $this->redirect($url);
    }

    private function renderPassports(AbiturientQuestionary $questionary)
    {
        $canEdit = $questionary->canEditQuestionary();
        $passports = new ActiveDataProvider([
            'query' => $questionary->getPassportData()
        ]);
        return $this->renderAjax(
            "../abiturient/questionary_partial/_document_grid",
            [
                'passports' => $passports,
                'canEdit' => $canEdit,
            ]
        );
    }

    public function actionDeletePassport()
    {
        $app_id = Yii::$app->request->post('appId');
        $application = BachelorApplication::findOne((int)$app_id);

        $user = Yii::$app->user->identity;
        $questionary = $application->abiturientQuestionary;
        $this->passportDataService->deletePassportData($user, $questionary);

        return $this->renderPassports($questionary);
    }

    



    public function actionSetPassport()
    {
        if (Yii::$app->request->isPost) {
            $app_id = Yii::$app->request->post('appId');
            $application = BachelorApplication::findOne($app_id);
            $questionary = $application->abiturientQuestionary;

            $user = Yii::$app->user->identity;
            $this->passportDataService->setPassportData($user, $questionary);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
        } else {
            return $this->asJson(['status' => true, 'messages' => []]);
        }
    }

    public function actionReturnToModerate(int $id, bool $remove_from_one_s)
    {
        $bachelorApplication = BachelorApplication::findOne(['id' => $id]);
        if (!$bachelorApplication) {
            throw new NotFoundHttpException();
        }

        if ($bachelorApplication->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('sandbox/errors', "Сообщение модератору при возврате к модерации архивного заявления: `Вы работаете с неактуальной версией заявления {fio}`", ['fio' => $bachelorApplication->fio]),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/sandbox/index']);
        }

        if (!($bachelorApplication->status == ApplicationInterface::STATUS_NOT_APPROVED || ($bachelorApplication->status == ApplicationInterface::STATUS_APPROVED && Yii::$app->configurationManager->getAllowReturnApprovedApplicationToModerating()))) {
            return $this->redirect(Url::toRoute(['/sandbox/view', 'id' => $bachelorApplication->id]));
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $bachelorApplication = $this->viewApplicationService->returnToModerate(Yii::$app->user->identity, $bachelorApplication, $remove_from_one_s);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при восстановлении статуса заявления\n" . print_r($bachelorApplication->errors, true));
            throw $e;
        }

        return $this->redirect(Url::toRoute(['/sandbox/moderate', 'id' => $bachelorApplication->id]));
    }

    public function actionParentForm()
    {
        $app_id = Yii::$app->request->post('appId');
        $application = BachelorApplication::findOne($app_id);
        $questionary = $application->abiturientQuestionary;
        $user = $questionary->user;

        $model = $this->parentDataService->getOrBuildParentData(
            $user,
            $questionary,
            'parentDataId'
        );
        $canEdit = $questionary->canEditQuestionary() && $application->type->moderator_allowed_to_edit && $application->canEdit();

        $action = Url::to(array_merge(
            ['/abiturient/set-parent-data'],
            $model->id ? ['id' => $model->id] : []
        ));

        return $this->renderAjax(
            '../abiturient/questionary_partial/parentData/_parentForm',
            [
                'model' => $model,
                'familyTypes' => $this->parentDataService->getFamilyTypes(),
                'passportTypes' => $this->parentDataService->getAllIdentityDocuments(),
                'document_type' => $this->parentDataService->getDocumentTypeID(),
                'keynum' => $model->id,
                'action' => $action,
                'canEdit' => $canEdit,
                'isReadonly' => !$canEdit,
                'application' => $application,
            ]
        );
    }

    public function actionSetParentData()
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $app_id = Yii::$app->request->post('appId');
        $application = BachelorApplication::findOne($app_id);
        $questionary = $application->abiturientQuestionary;
        $user = $questionary->user;

        $parentData = $this->parentDataService->getOrBuildParentData(
            $user,
            $questionary,
            'ParentData.id'
        );
        [
            'parentData' => $parentData,
            'addressData' => $addressData,
            'passportData' => $passportData,
            'personalData' => $personalData,
        ] = $this->parentDataService->loadParentData(
            $parentData
        );

        $transaction = $parentData->getDb()->beginTransaction();

        try {
            $success = $this->parentDataService->setParentData(
                $user,
                $parentData,
                $passportData,
                $personalData,
                $addressData
            );

            if (!$success) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = 400;
                return Html::errorSummary([$personalData, $addressData, $passportData, $parentData], [
                    'header' => '<div class="alert alert-danger">',
                    'footer' => '</div>'
                ]);
            } else {
                $transaction->commit();
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->renderParents($questionary);
    }

    private function renderParents(AbiturientQuestionary $questionary)
    {
        return $this->renderAjax("../abiturient/questionary_partial/parentData/_parent_grid", [
            'parents' => new ActiveDataProvider([
                'query' => $questionary->getParentData()
            ]),
            'canEdit' => $questionary->canEditQuestionary(),
        ]);
    }

    public function actionDeleteParentData()
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $app_id = Yii::$app->request->post('appId');
        $application = BachelorApplication::findOne($app_id);
        $questionary = $application->abiturientQuestionary;
        $user = Yii::$app->user->identity;

        $id = Yii::$app->request->post('parentDataId');

        $parentData = ParentData::findOne($id);
        if ($parentData != null) {
            $this->parentDataService->checkAccessibility($user, $parentData->questionary_id);

            $parentData->archive();
        }

        $this->parentDataService->parentDataChangedEvent($user);

        return $this->renderParents($questionary);
    }

    public function actionFullPackageXml(int $id)
    {
        $app = BachelorApplication::findOne($id);
        $fio = $app->user->getPublicIdentity();
        if (!empty($app)) {
            $cur_date = date('d.m.Y H:i:s');
            return Yii::$app->response->sendContentAsFile(
                ArrayToXmlConverter::to_xml((new FullApplicationPackageBuilder($app))->build(), 'EntrantPackage'),
                "{$app->type->name}({$app->type->campaignCode})_{$fio}_{$cur_date}.xml"
            );
        }
        return false;
    }

    




    public function actionInforming(string $name)
    {
        return $this->render('informing', [
            'message' => ManagerInforming::getMessage($name)
        ]);
    }

    



    public function actionViewArchiveApplication(int $id, int $user_id)
    {
        return $this->render(
            'view-archive-application',
            [
                'id' => $id,
                'applicationNodes' => $this->viewApplicationService->getArchiveApplicationForView($user_id, $id, $this),
                'currentUser' => Yii::$app->user->identity,
            ]
        );
    }

    public function actionIsBlocked()
    {
        $id = Yii::$app->request->get('id');
        
        $application = BachelorApplication::findOne(['id' => $id]);
        $isBlocked = !$application ? true : $application->block_status == 1;

        return $this->asJson(['isBlocked' => $isBlocked]);
    }

    public function actionValidateApplication(int $id)
    {
        $result = [];
        $application = BachelorApplication::findOne($id);
        if ($application) {
            $result = (new CheckAllApplication())->checkAllApplication($application, false);
        }

        return $this->asJson($result);
    }

    public function actionEnrollmentRejection()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'index',
            $this->sandboxApplicationsTableService->buildAppicationsDataForSandbox($user, 'enrollment-rejection')
        );
    }
}
