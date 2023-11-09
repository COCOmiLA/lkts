<?php

namespace common\modules\abiturient\controllers;

use common\commands\command\AddToTimelineCommand;
use common\components\behaviors\emailConfirmBehavior\EmailConfirmBehavior;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\CheckAllApplication;
use common\modules\abiturient\models\CommentsComing;
use common\modules\abiturient\models\repositories\SpecialityRepository;
use common\services\abiturientController\bachelor\accounting_benefits\AccountingBenefitsService;
use common\services\abiturientController\bachelor\accounting_benefits\BenefitsService;
use common\services\abiturientController\bachelor\accounting_benefits\OlympiadsService;
use common\services\abiturientController\bachelor\accounting_benefits\TargetReceptionsService;
use common\services\abiturientController\bachelor\AdmissionAgreementService;
use common\services\abiturientController\bachelor\Application1CProcessorService;
use common\services\abiturientController\bachelor\bachelorSpeciality\BachelorSpecialityService;
use common\services\abiturientController\bachelor\bachelorSpeciality\SpecialityPrioritiesService;
use common\services\abiturientController\bachelor\ChangeHistoryService;
use common\services\abiturientController\bachelor\CommentService;
use common\services\abiturientController\bachelor\EducationService;
use common\services\abiturientController\bachelor\entrant_test\CentralizedTestingService;
use common\services\abiturientController\bachelor\entrant_test\EntrantTestService;
use common\services\abiturientController\bachelor\IndividualAchievementService;
use common\services\abiturientController\bachelor\LoadScansService;
use common\services\abiturientController\bachelor\PaidContractService;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\base\UserException;
use yii\bootstrap4\Alert;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;




class BachelorController extends Controller
{
    private EducationService $educationService;
    private SpecialityPrioritiesService $specialityPrioritiesService;

    
    private AccountingBenefitsService $accountingBenefitsService;

    
    private BenefitsService $benefitsService;

    
    private OlympiadsService $olympiadsService;

    
    private TargetReceptionsService $targetReceptionsService;

    
    private EntrantTestService $entrantTestService;

    
    private IndividualAchievementService $individualAchievementService;

    
    private CommentService $commentService;

    
    private LoadScansService $loadScansService;

    
    private ChangeHistoryService $changeHistoryService;

    
    private BachelorSpecialityService $bachelorSpecialityService;

    
    private AdmissionAgreementService $admissionAgreementService;

    
    private Application1CProcessorService $application1CProcessorService;

    
    private CentralizedTestingService $centralizedTestingService;

    
    private PaidContractService $paidContractService;

    public function getViewPath()
    {
        return Yii::getAlias('@common/modules/abiturient/views/bachelor');
    }

    




















    public function __construct(
        $id,
        $module,
        CommentService $commentService,
        BenefitsService $benefitsService,
        EducationService $educationService,
        LoadScansService $loadScansService,
        OlympiadsService $olympiadsService,
        EntrantTestService $entrantTestService,
        PaidContractService $paidContractService,
        ChangeHistoryService $changeHistoryService,
        TargetReceptionsService $targetReceptionsService,
        AccountingBenefitsService $accountingBenefitsService,
        AdmissionAgreementService $admissionAgreementService,
        BachelorSpecialityService $bachelorSpecialityService,
        CentralizedTestingService $centralizedTestingService,
        SpecialityPrioritiesService $specialityPrioritiesService,
        IndividualAchievementService $individualAchievementService,
        Application1CProcessorService $application1CProcessorService,
        $config = []
    ) {
        $this->commentService = $commentService;
        $this->benefitsService = $benefitsService;
        $this->educationService = $educationService;
        $this->loadScansService = $loadScansService;
        $this->olympiadsService = $olympiadsService;
        $this->entrantTestService = $entrantTestService;
        $this->paidContractService = $paidContractService;
        $this->changeHistoryService = $changeHistoryService;
        $this->targetReceptionsService = $targetReceptionsService;
        $this->accountingBenefitsService = $accountingBenefitsService;
        $this->admissionAgreementService = $admissionAgreementService;
        $this->bachelorSpecialityService = $bachelorSpecialityService;
        $this->centralizedTestingService = $centralizedTestingService;
        $this->specialityPrioritiesService = $specialityPrioritiesService;
        $this->individualAchievementService = $individualAchievementService;
        $this->application1CProcessorService = $application1CProcessorService;

        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'accounting-benefits',
                            'add-agree',
                            'add-paid-contract',
                            'application-change-history',
                            'application-infinite-scroll-history',
                            'application',
                            'autofill-specialty',
                            'check-can-send-application',
                            'comment',
                            'consults',
                            'create-application',
                            'decline-agreement',
                            'delete-centralized-testing',
                            'download-attached-paid-contract',
                            'edu-docs',
                            'edu-levels',
                            'edu-profile',
                            'education',
                            'ege-save-result',
                            'ege',
                            'exams',
                            'load-scans',
                            'make-copy',
                            'olympiads-list',
                            'preference-list',
                            'print-application-by-full-package',
                            'print-application-return-form',
                            'print-enrollment-rejection-form',
                            'printforms',
                            'reload-ege',
                            'remove-agreement-decline',
                            'remove-attached-paid-contract',
                            'save-attached-application-files',
                            'send-application',
                            'validate-application',
                            'get-available-parent-specialities',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_ABITURIENT]
                    ],
                    [
                        'actions' => [
                            'application-change-history',
                            'application-infinite-scroll-history',
                            'download-attached-paid-contract',
                            'edu-docs',
                            'edu-levels',
                            'edu-profile',
                            'olympiads-list',
                            'preference-list',
                            'reload-ege',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ],
                    [
                        'actions' => [
                            'application-change-history',
                            'application-infinite-scroll-history',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_VIEWER]
                    ],
                    [
                        'actions' => [
                            'add-specialities',
                            'define-discipline-set',
                            'delete-education',
                            'removespeciality',
                            'reorderspeciality',
                            'save-education',
                            'update-full-package',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER, User::ROLE_ABITURIENT]
                    ],
                ],
            ],
            [
                'class' => EmailConfirmBehavior::class,
                'user' => Yii::$app->user->identity
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save-education' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function actionCreateApplication()
    {
        $request = Yii::$app->request;
        $user = Yii::$app->user->identity;

        if (!$user->canMakeStep('make-application') || !$request->isPost) {
            return $this->redirect('/abiturient/questionary', 302);
        }
        $application_type = $this->application1CProcessorService->getApplicationTypeFromPost($user);

        if (
            !$application_type ||
            !$application_type->haveStageOne()
        ) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        [$questionary_filled_for_this_type, $error_message] = (new CheckAllApplication())->validateAbiturientQuestionary($user->abiturientQuestionary, [$application_type]);
        if (
            $questionary_filled_for_this_type &&
            $bachelorApplication = $this->application1CProcessorService->createBachelorApplication($user, $application_type)
        ) {
            return $this->redirect(Url::toRoute(['bachelor/education', 'id' => $bachelorApplication->id]));
        } elseif (trim((string)$error_message)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t(
                    'abiturient/header',
                    'Текст ошибки при выборе ПК: `Для подачи заявления в данную приёмную кампанию необходимо заполнить: {errorMessage}`',
                    ['errorMessage' => $error_message]
                ),
                'options' => ['class' => 'alert-danger']
            ]);
        }

        return $this->redirect('/abiturient/questionary', 302);
    }

    public function actionEducation(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->educationService->checkAccessibility($user, $id);
        $application = $this->educationService->getApplication($id);

        if (!$this->educationService->checkIfAbiturientQuestionaryFilled($application)) {
            Yii::$app->session->setFlash('needToSaveQuestionary', 'true');
            return $this->redirect('/abiturient/questionary', 302);
        }

        $canEdit = $application->canEdit();
        [
            'attachments' => $attachments,
            'regulations' => $regulations,
        ] = $this->educationService->getRegulationsAndAttachmentsForEducation($application);
        [
            'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
            'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
            'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
        ] = $this->educationService->getFileControlFlags($application);
        [
            'isAttachmentsAdded' => $isAttachmentsAdded,
            'attachmentErrors' => $attachmentErrors,
        ] = $this->educationService->checkAttachmentFiles($application, $canEdit);

        if (Yii::$app->request->isPost) {
            try {
                [
                    'hasChanges' => $hasChanges,
                    'attachments' => $attachments,
                    'regulations' => $regulations,
                ] = $this->educationService->postProcessingRegulationsAndAttachments(
                    $application,
                    $attachments,
                    $regulations
                );
            } catch (Throwable $th) {
                $this->educationService->processErrorMessageProcessingSavingAttachment($th, 'BachelorController.actionEducation');
            }

            if (!$hasChanges) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                    'options' => ['class' => 'alert-warning']
                ]);
            } else if (!Yii::$app->user->identity->isModer()) {
                $nextStepUrl = $this->educationService->getNextStep($application);
                if ($nextStepUrl) {
                    return $this->redirect($nextStepUrl);
                }
            }
        }

        return $this->render(
            'education',
            [
                'canEdit' => $canEdit,
                'application_comparison' => $this->educationService->getApplicationComparison($user, $application),
                'education_datum' => $application->getEducations()->with(['application.user'])->all(),
                'status' => Yii::$app->session->getFlash('educationSaved', false),
                'hasChangedAttributes' => Yii::$app->session->getFlash('hasChangedAttributes', null),
                'attachments' => $attachments,
                'isAttachmentsAdded' => $isAttachmentsAdded,
                'attachmentErrors' => $attachmentErrors,
                'application' => $application,
                'regulations' => $regulations,
                'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
                'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
                'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
            ]
        );
    }

    public function actionSaveEducation(int $app_id, int $edu_id = null)
    {
        $user = Yii::$app->user->identity;
        $this->educationService->checkAccessibility($user, $app_id);
        $application = $this->educationService->getApplication($app_id);

        $is_manager = Yii::$app->user->identity->isModer();
        if (is_null($application)) {
            throw new NotFoundHttpException('Не найдено указанное заявление');
        }
        if ($is_manager && !$application->type->moderator_allowed_to_edit) {
            throw new ForbiddenHttpException('Модератору не разрешено вносить изменения');
        }

        $education = $this->educationService->getEducation($application, $edu_id);

        [
            'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
            'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
            'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
        ] = $this->educationService->getFileControlFlags($application);

        $error_msg = '';
        if (
            ($is_manager ||
                $allowAddNewEducationAfterApprove ||
                $allowAddNewFileToEducationAfterApprove ||
                $allowDeleteFileFromEducationAfterApprove ||
                $application->canEdit()) &&
            !$education->hasEnlistedBachelorSpecialities()
        ) {
            $education->load(Yii::$app->request->post());
            $this->educationService->setContractor($education);

            if ($education->validate()) {
                [
                    'education' => $education,
                    'educationSaved' => $educationSaved,
                    'hasChangedAttributes' => $hasChangedAttributes,
                ] = $this->educationService->educationSaveProcess($application, $education, $is_manager);

                Yii::$app->session->setFlash('hasChangedAttributes', $hasChangedAttributes);
                if ($educationSaved) {
                    Yii::$app->session->setFlash('educationSaved', $hasChangedAttributes);

                    if (
                        !$is_manager &&
                        $warningAlertBody = $this->educationService->afterEducationSaveProcessAsNotModerator($application, $education)
                    ) {
                        Yii::$app->session->setFlash('alert', [
                            'body' => $warningAlertBody,
                            'options' => ['class' => 'alert-warning']
                        ]);
                    }
                }
            } else {
                $error_msg = implode(
                    '\n',
                    array_filter(array_map(
                        function ($error) {
                            return is_array($error) ? array_values($error)[0] : $error;
                        },
                        $education->errors
                    ))
                );
                Yii::$app->session->setFlash('educationErrors', $error_msg);
            }
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } else {
            return $this->asJson(['status' => true, 'messages' => []]);
        }
    }

    public function actionDeleteEducation(int $app_id, int $edu_id)
    {
        $user = Yii::$app->user->identity;
        $this->educationService->checkAccessibility($user, $app_id);
        $application = $this->educationService->getApplication($app_id);

        $this->educationService->deleteEducation($application, $edu_id);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    public function actionEge(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->entrantTestService->checkAccessibility($user, $id);
        $application = $this->entrantTestService->getApplication($id);

        if (!$this->entrantTestService->checkIfAbiturientQuestionaryFilled($application)) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        $canEdit = $application->canEdit() && $application->canEditSpecialities();

        [
            'attachments' => $attachments,
            'regulations' => $regulations,
        ] = $this->entrantTestService->getRegulationsAndAttachmentsForEntrantTest($application);

        $egeResult = new EgeResult();
        $egeResult->application_id = $application->id;
        $competitiveGroupEntranceTest = DictionaryCompetitiveGroupEntranceTest::getDataProviderByApplication($application);

        return $this->render(
            'ege',
            [
                'application_comparison' => $this->entrantTestService->getApplicationComparison($user, $application),
                'canEdit' => $canEdit,
                'results' => $application->getSavedEgeResults(),
                'regulations' => $regulations,
                'application' => $application,
                'attachments' => $attachments,
                'competitiveGroupEntranceTest' => $competitiveGroupEntranceTest,
                'newEgeResult' => $egeResult,
            ]
        );
    }

    public function actionEgeSaveResult(int $id)
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['ege', 'id' => $id], 302);
        }

        $user = Yii::$app->user->identity;
        $this->entrantTestService->checkAccessibility($user, $id);
        $application = $this->entrantTestService->getApplication($id);

        if (!$this->entrantTestService->checkIfAbiturientQuestionaryFilled($application)) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        [
            'attachments' => $attachments,
            'regulations' => $regulations,
        ] = $this->entrantTestService->getRegulationsAndAttachmentsForEntrantTest($application);

        if (Yii::$app->request->isPost) {
            try {
                [
                    'hasChanges' => $hasChanges,
                    'attachments' => $attachments,
                    'regulations' => $regulations,
                ] = $this->entrantTestService->postProcessingRegulationsAndAttachments(
                    $application,
                    $attachments,
                    $regulations
                );
            } catch (Throwable $th) {
                $this->entrantTestService->processErrorMessageProcessingSavingAttachment($th, 'BachelorController.actionEgeSaveResult');
            }

            if (isset($hasChanges) && !$hasChanges) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                    'options' => ['class' => 'alert-warning']
                ]);
            } else if (!Yii::$app->user->identity->isModer()) {
                $nextStepUrl = $this->entrantTestService->getNextStep($application);
                if ($nextStepUrl) {
                    return $this->redirect($nextStepUrl);
                }
            }
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionComment(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->commentService->checkAccessibility($user, $id);
        $application = $this->commentService->getApplication($id);

        if (!$this->commentService->checkIfAbiturientQuestionaryFilled($application)) {
            Yii::$app->session->setFlash('needToSaveQuestionary', 'true');
            return $this->redirect('/abiturient/questionary', 302);
        }

        if (!($model = CommentsComing::findOne(['bachelor_application_id' => $id]))) {
            $model = new CommentsComing();
        }

        $comSaved = null;
        $alertBody = null;
        $alertClass = null;
        $comNotSaved = null;
        if (Yii::$app->request->isPost) {
            [
                'comSaved' => $comSaved,
                'alertBody' => $alertBody,
                'alertClass' => $alertClass,
                'comNotSaved' => $comNotSaved,
            ] = $this->commentService->commentPostProcessing($model, $application, $user->id);
        }

        if ($comSaved) {
            Yii::$app->session->setFlash('comSaved', $comSaved);
        }
        if ($comNotSaved) {
            Yii::$app->session->setFlash('comNotSaved', $comNotSaved);
        }

        if ($alertBody && $alertClass) {
            Yii::$app->session->setFlash('alert', [
                'body' => $alertBody,
                'options' => ['class' => $alertClass]
            ]);
        }

        return $this->render('comment', [
            'application' => $application,
            'model' => $model,
        ]);
    }

    public function actionApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $id);
        $application = $this->bachelorSpecialityService->getApplication($id);

        if ($application == null) {
            return $this->redirect('/abiturient/applications', 302);
        }

        if (!$this->bachelorSpecialityService->checkIfAbiturientQuestionaryFilled($application)) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        $add_errors_json = Yii::$app->session->get('add_errors');
        $add_errors = json_decode((string)$add_errors_json);
        Yii::$app->session->remove('add_errors');

        $canEdit = $application->canEdit();
        [
            'attachments' => $attachments,
            'regulations' => $regulations,
        ] = $this->bachelorSpecialityService->getRegulationsAndAttachmentsForEducation($application);

        if ($canEdit && !$this->specialityPrioritiesService->checkPrioritiesSettled($application)) {
            $this->specialityPrioritiesService->setUpPriorities($application);
        }
        if (!$user->canMakeStep('specialities', $application)) {
            return $this->redirect(['/bachelor/education', 'id' => $id]);
        }

        
        $specialities = $this->bachelorSpecialityService->getSelectedSpecialityList($application);

        if (Yii::$app->request->isPost && $canEdit) {
            [$spec_changed, $has_errors] = $this->bachelorSpecialityService->processLoadedData(
                $application,
                $specialities,
                Yii::$app->request->post(),
                true
            );

            if (!$has_errors && !$user->isModer()) {
                $nextStepUrl = $this->bachelorSpecialityService->getNextStep($application);
                if ($nextStepUrl) {
                    return $this->redirect($nextStepUrl);
                }
            }
            if (!$spec_changed) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                    'options' => ['class' => 'alert-warning']
                ]);
            }
        }

        $this->bachelorSpecialityService->validateAllSpecialities($specialities);

        $spec_Ids = ArrayHelper::getColumn($specialities, 'id');
        $agrees = AdmissionAgreement::find()->active()->andWhere(['in', 'speciality_id', $spec_Ids])->all();
        $hasAgree = (bool)$agrees;

        $this->bachelorSpecialityService->checkUpdateContractDocsFrom1C($application, $specialities);

        $application->setScenario(BachelorApplication::SCENARIO_APPLICATION_WITH_EDUCATION);
        return $this->render(
            'application',
            ArrayHelper::merge(
                [
                    'hasAgree' => $hasAgree,
                    'add_errors' => $add_errors,
                    'application' => $application,
                    'attachments' => $attachments,
                    'regulations' => $regulations,
                    'specialities' => $specialities,
                    'isPost' => Yii::$app->request->isPost,
                    'target_receptions' => ArrayHelper::map($application->bachelorTargetReceptions, 'id', 'name'),
                    'limit_type' => $application->type->campaign->getMaxSpecialityType(),
                    'max_speciality_count' => $application->type->campaign->max_speciality_count,
                    'available_specialities' => $this->bachelorSpecialityService->getAvailableSpecialityList($application),
                    'application_comparison' => $this->bachelorSpecialityService->getApplicationComparison($user, $application),
                    'bachelorSpecialityService' => $this->bachelorSpecialityService,
                    'specialityPrioritiesService' => $this->specialityPrioritiesService,
                ],
                SpecialityRepository::getSpecialityFiltersData($application)
            )
        );
    }

    public function actionLoadScans(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->loadScansService->checkAccessibility($user, $id);
        $application = $this->loadScansService->getApplication($id);
        if ($application == null) {
            return $this->redirect('/abiturient/applications', 302);
        }
        if (!$this->loadScansService->checkIfAbiturientQuestionaryFilled($application)) {
            Yii::$app->session->setFlash('needToSaveQuestionary', 'true');
            return $this->redirect('/abiturient/questionary', 302);
        }
        $canEdit = $application->canEdit();

        [
            'regulations' => $regulations,
            'attachments' => $attachments,
        ] = $this->loadScansService->getAllRegulationsAndAttachments($application);

        if (Yii::$app->request->isPost) {
            try {
                [
                    'hasChanges' => $hasChanges,
                    'attachments' => $attachments,
                    'regulations' => $regulations,
                ] = $this->loadScansService->postProcessingRegulationsAndAttachments(
                    $application,
                    $attachments,
                    $regulations
                );
            } catch (Throwable $th) {
                $this->loadScansService->processErrorMessageProcessingSavingAttachment($th, 'BachelorController.actionLoadScans');
            }

            if (!$hasChanges) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                    'options' => ['class' => 'alert-warning']
                ]);
            } else if (!Yii::$app->user->identity->isModer()) {
                $nextStepUrl = $this->loadScansService->getNextStep($application);
                if ($nextStepUrl) {
                    return $this->redirect($nextStepUrl);
                }
            }
        }
        [
            'attachmentErrors' => $attachmentErrors,
            'isAttachmentsAdded' => $isAttachmentsAdded,
        ] = $this->loadScansService->checkAttachmentFiles($application, $canEdit);

        return $this->render(
            'load-scans',
            [
                'application' => $application,
                'regulations' => $regulations,
                'attachmentErrors' => $attachmentErrors,
                'isAttachmentsAdded' => $isAttachmentsAdded,
                'full_attachments_package' => $attachments,
            ]
        );
    }

    public function actionSendApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->application1CProcessorService->checkAccessibility($user, $id);
        $application = $this->application1CProcessorService->getApplication($id);

        if ($application->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('abiturient/errors', 'Сообщение поступающему при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления`'),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/abiturient/applications']);
        }
        $applicationErrors = (new CheckAllApplication())->checkAllApplication($application);

        $moderating_now = false;
        $hasError = !empty($applicationErrors);

        if (!$hasError && !$application->canBeSentToModerate()) {
            $moderating_now = true;
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::$app->configurationManager->getText('sending_error_because_of_moderating_now', $application->type ?? null),
                'options' => ['class' => 'alert-danger']
            ]);
        }
        if (
            !$hasError &&
            !$moderating_now &&
            $application->canEdit() &&
            $application->specialities
        ) {
            $hasError = true;
            $addToTimelineCommandConfig = $this->application1CProcessorService->sendApplicationTo1C($user, $application);
            if ($addToTimelineCommandConfig) {
                $hasError = false;
                Yii::$app->commandBus->handle(new AddToTimelineCommand($addToTimelineCommandConfig));
            }
        }

        Yii::$app->session->setFlash('applicationHasError', $hasError);

        return $this->redirect($hasError ? Yii::$app->request->referrer : ['abiturient/applications']);
    }

    public function actionPreferenceList(int $app_id, $id)
    {
        $user = Yii::$app->user->identity;
        $this->benefitsService->checkAccessibility($user, $app_id);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'output' => $this->benefitsService->getPreferenceListForSelect($app_id),
            'selected' => ''
        ];
    }

    public function actionOlympiadsList(int $app_id, $id)
    {
        $user = Yii::$app->user->identity;
        $this->olympiadsService->checkAccessibility($user, $app_id);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'output' => $this->olympiadsService->getOlympiadsListSelect($app_id, $id),
            'selected' => ''
        ];
    }

    


    public function actionDeleteCentralizedTesting(int $id, $app_id)
    {
        $user = Yii::$app->user->identity;
        $this->centralizedTestingService->checkAccessibility($user, $app_id);
        $application = $this->centralizedTestingService->getApplication($app_id);

        if (!$application) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t(
                    'abiturient/applications/all',
                    'Текст алерта ошибки доступа при попытке архивирования результатов ЦТ; на страницы заявлений поступающего: `<strong>Ошибка доступа!</strong> Вам не разрешено производить данное действие.`'
                ),
                'options' => ['class' => 'alert-danger']
            ]);

            return $this->redirect('/abiturient/applications', 302);
        }

        $centralizedTesting = $this->centralizedTestingService->getCentralizedTesting($application, $id);
        if (!$centralizedTesting) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t(
                    '/bachelor/ege/all',
                    'Текст алерта ошибки не нахождения ЦТ при попытке архивирования результатов ЦТ; на странице ВИ: `<strong>Ошибка!</strong> Запись не обнаружена.`'
                ),
                'options' => ['class' => 'alert-danger']
            ]);

            return $this->redirect(Url::to(['/bachelor/ege', 'id' => $application->id]));
        }

        $centralizedTesting->archive();

        Yii::$app->session->setFlash('alert', [
            'body' => Yii::t(
                '/bachelor/ege/all',
                'Текст алерта успешного архивирования результатов ЦТ; на странице ВИ: `Запись успешно удалена.`'
            ),
            'options' => ['class' => 'alert-success']
        ]);

        return $this->redirect(Url::to(['/bachelor/ege', 'id' => $application->id]));
    }

    public function actionGetAvailableParentSpecialities()
    {
        $applicationId = $this->request->post('applicationId');
        $this->response->format = Response::FORMAT_JSON;
        if (!$applicationId) {
            return [];
        }
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $applicationId);
        $application = $this->bachelorSpecialityService->getApplication($applicationId);
        if (!$application->canEdit() || !$application->canEditSpecialities()) {
            return [];
        }
        $specialities = $application->specialities;
        $speciality_ids = ArrayHelper::getColumn($specialities, 'speciality_id');
        $result = [];
        $processed_parent_spec_ids = [];
        foreach ($specialities as $speciality) {
            if ($speciality->speciality->parentCombinedCompetitiveGroupRef) {
                $parent_speciality = $speciality->speciality->parentCombinedCompetitiveGroupRefSpeciality;
                if ($parent_speciality && !$application->hasSpeciality($parent_speciality->id)) {
                    if (in_array($parent_speciality->id, $processed_parent_spec_ids)) {
                        continue;
                    }
                    $all_child_specs = $parent_speciality->getChildrenCombinedCompetitiveGroupRefSpecialities()->select('id')->column();
                    if (count(array_intersect($all_child_specs, $speciality_ids)) != count($all_child_specs)) {
                        continue;
                    }


                    $processed_parent_spec_ids[] = $parent_speciality->id;
                    $result[] = [
                        'id' => $parent_speciality->id,
                        'name' => $parent_speciality->competitiveGroupRef->reference_name,
                    ];
                }
            }
        }
        return $result;
    }

    public function actionAddSpecialities(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $id);
        $application = $this->bachelorSpecialityService->getApplication($id);

        $errors = [];
        if ($application == null) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        if (Yii::$app->request->isPost && $application->canEdit()) {
            if (Yii::$app->request->post('spec') != null) {
                $postSpecData = Yii::$app->request->post('spec');
                $postSpecialityOrder = [];
                if (EmptyCheck::isNonEmptyJson(Yii::$app->request->post('spec_order'))) {
                    $postSpecialityOrder = json_decode(Yii::$app->request->post('spec_order'));
                }
                $errors = $this->specialityPrioritiesService->addSpecialitiesByIds(
                    $application,
                    $postSpecData,
                    $postSpecialityOrder
                );
            }
        }
        if ($errors) {
            Yii::$app->session->set('add_errors', json_encode($errors));
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRemovespeciality(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $id);
        $application = $this->bachelorSpecialityService->getApplication($id);

        $is_manager = $user->isModer();
        if (!$application) {
            if ($is_manager) {
                $this->redirect('/sandbox/index');
            } else {
                $this->redirect('/abiturient/questionary');
            }
        }

        $redirect_to = $is_manager
            ? Url::toRoute(['/sandbox/moderate', 'id' => $application->id])
            : Url::toRoute(['/bachelor/application', 'id' => $application->id]);

        if (Yii::$app->request->isPost && $application->canEdit()) {
            $spec = $this->bachelorSpecialityService->getBachelorSpecialityFromPostByApplication($application);
            [
                'hasError' => $hasError,
                'errorMessage' => $errorMessage
            ] = $this->bachelorSpecialityService->checkCanRemoveSpeciality($spec);

            if ($hasError) {
                Yii::$app->session->setFlash(
                    'consentAddErrors',
                    $errorMessage
                );

                return $this->redirect($redirect_to);
            }

            
            if ($user->id == $application->user_id || $is_manager) {
                $this->specialityPrioritiesService->removeSpeciality($application, $spec);
                
                
                if (!$is_manager) {
                    $application->resetStatus();
                }
            }
        }

        return $this->redirect($redirect_to);
    }

    public function actionReorderspeciality(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $id);
        $application = $this->bachelorSpecialityService->getApplication($id);

        $is_manager = $user->isModer();
        if (!$application) {
            if ($is_manager) {
                $this->redirect('/sandbox/index');
            } else {
                $this->redirect('/abiturient/questionary');
            }
        }

        $redirect_to = $is_manager
            ? Url::toRoute(['/sandbox/moderate', 'id' => $application->id])
            : Url::toRoute(['/bachelor/application', 'id' => $application->id]);

        if (Yii::$app->request->isPost && $application->canEdit()) {
            $move_type = Yii::$app->request->post("type");
            $spec = $this->bachelorSpecialityService->getBachelorSpecialityFromPostByApplication($application);
            if ($spec && $spec->canEdit()) {
                $this->specialityPrioritiesService->changeSpecialityPriority($application, $spec, $move_type);
                
                
                if (!$is_manager) {
                    $application->resetStatus();
                }
            }
        }

        return $this->redirect($redirect_to);
    }

    public function actionAutofillSpecialty($application_id = null)
    {
        

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            Yii::$app->response->statusCode = 400;

            Yii::error(
                'Ошибка добавления КГ "На общих основаниях", т.к. использовался метод передачи данных отличный от POST: ',
                'BachelorControllerForAutofillSpeciality.actionAutofillSpecialty'
            );

            $alert = Alert::widget([
                'options' => ['class' => 'alert-danger'],
                'body' => Yii::t(
                    'abiturient/header/autofill-specialty-modal',
                    'Текст сообщения об ошибке, если использовался метод передачи данных отличный от POST; в модальном окне автозаполнения НП на панели навигации ЛК: `Не удалось обработать данные. Обратитесь к администратору`'
                ),
            ]);
            return ['error' => $alert];
        }

        $user = Yii::$app->user->identity;

        $data = [
            'archive' => false,
            'user_id' => $user->id,
            'id' => $application_id,
        ];
        $application = BachelorApplication::findOne($data);
        if (!$application) {
            Yii::$app->response->statusCode = 400;

            Yii::error(
                'Ошибка добавления КГ "На общих основаниях", т.к. заявление не найдено по запросу: ' .
                    print_r($data, true),
                'BachelorControllerForAutofillSpeciality.actionAutofillSpecialty'
            );

            $alert = Alert::widget([
                'options' => ['class' => 'alert-danger'],
                'body' => Yii::t(
                    'abiturient/header/autofill-specialty-modal',
                    'Текст сообщения об ошибке, если не удалось нейти заявление; в модальном окне автозаполнения НП на панели навигации ЛК: `Не удалось найти заявление. Обратитесь к администратору`'
                ),
            ]);
            return ['error' => $alert];
        }

        Yii::$app->session->set('isAutofillSpecialty', true);

        $allSpecialtyIds = ArrayHelper::getValue(Yii::$app->request->post(), 'allSpecialtyIds', []);
        $selectedSpecialtyIds = ArrayHelper::getValue(Yii::$app->request->post(), 'selectedSpecialtyIds', []);

        $notSelectedSpecialtyIds = array_diff($allSpecialtyIds, $selectedSpecialtyIds);
        if ($notSelectedSpecialtyIds) {
            Yii::$app->session->set('notSelectedSpecialtyIds', $notSelectedSpecialtyIds);
        }

        if (!$selectedSpecialtyIds) {
            Yii::$app->response->statusCode = 200;

            return ['message' => ''];
        }

        $postSpecData = array_unique($selectedSpecialtyIds);
        $errors = $this->specialityPrioritiesService->addSpecialitiesByIds($application, $postSpecData);

        if ($errors) {
            Yii::$app->response->statusCode = 400;

            Yii::error(
                'Ошибка добавления КГ "На общих основаниях", т.к. возникли ошибки при автоматическом добавлении НП: ' .
                    print_r(Yii::$app->session->get('add_errors'), true),
                'BachelorControllerForAutofillSpeciality.actionAutofillSpecialty'
            );

            $alert = Alert::widget([
                'options' => ['class' => 'alert-danger'],
                'body' => Yii::t(
                    'abiturient/header/autofill-specialty-modal',
                    'Текст сообщения об ошибке, если не удалось добавить КГ "На общих основаниях"; в модальном окне автозаполнения НП на панели навигации ЛК: `Ошибка добавления направления. Повторите попытку позже.`'
                ),
            ]);
            return ['error' => $alert];
        }

        Yii::$app->response->statusCode = 200;

        return ['message' => ''];
    }

    public function actionAddPaidContract($spec_id)
    {
        $user = Yii::$app->user->identity;
        $speciality = BachelorSpeciality::findOne($spec_id);

        $this->paidContractService->checkAccessibility($user, $speciality->application_id);
        $application = $this->paidContractService->getApplication($speciality->application_id);

        $this->paidContractService->uploadAttachment(
            $user,
            $application,
            $speciality
        );

        return $this->redirect(Url::to(['/bachelor/application', 'id' => $application->id]), 302);
    }

    public function actionDownloadAttachedPaidContract(int $id)
    {
        $user = Yii::$app->user->identity;
        $speciality = BachelorSpeciality::findOne($id);

        $this->paidContractService->checkAccessibility($user, $speciality->application_id);
        $application = $this->paidContractService->getApplication($speciality->application_id);

        [
            'path' => $path,
            'fileName' => $fileName,
        ] = $this->paidContractService->getPathAndNameAttachment($speciality);
        if ($path && $fileName) {
            return Yii::$app->response->sendFile($path, $fileName);
        }

        return $this->redirect(Url::to(['/bachelor/application', 'id' => $application->id]), 302);
    }

    public function actionRemoveAttachedPaidContract(int $id)
    {
        $user = Yii::$app->user->identity;
        $speciality = BachelorSpeciality::findOne($id);

        $this->paidContractService->checkAccessibility($user, $speciality->application_id);
        $application = $this->paidContractService->getApplication($speciality->application_id);

        if ($speciality && $attached = $speciality->getAttachedPaidContract()) {
            $attached->safeDelete($user); 
        }

        return $this->redirect(Url::to(['/bachelor/application', 'id' => $application->id]), 302);
    }

    public function actionAddAgree()
    {
        $goBackRedirect = $this->goBack(
            !empty(Yii::$app->request->referrer)
                ? Yii::$app->request->referrer
                : null
        );

        $user = Yii::$app->user->identity;
        $spec_id = (int)Yii::$app->request->post('spec_id');
        $speciality = BachelorSpeciality::findOne($spec_id);
        if (
            !$speciality ||
            !Yii::$app->request->isPost
        ) {
            return $goBackRedirect;
        }

        $id = $speciality->application_id;

        $this->admissionAgreementService->checkAccessibility($user, $id);
        $application = $this->admissionAgreementService->getApplication($id);
        $goBackRedirect = $this->redirect(Url::toRoute(['/bachelor/application', 'id' => $application->id]), 302);

        [
            'canEdited' => $canEdited,
            'consentAddErrors' => $consentAddErrors,
        ] = $this->admissionAgreementService->checkAgreementAccessibility($application, $speciality);
        if (!$canEdited) {
            Yii::$app->session->setFlash('consentAddErrors', $consentAddErrors);

            return $goBackRedirect;
        }

        $this->admissionAgreementService->createAgreements($user, $application, $speciality);

        return $goBackRedirect;
    }

    public function actionEduLevels()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'output' => $this->educationService->getEducationLevelsDataForSelect(),
            'selected' => ''
        ];
    }

    public function actionEduDocs()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'output' => $this->educationService->getEducationDocsDataForSelect(),
            'selected' => ''
        ];
    }

    public function actionEduProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        [
            'output' => $output,
            'selected' => $selectedProfile,
        ] = $this->educationService->getEducationProfileDataForSelect();

        return [
            'output' => $output,
            'selected' => $selectedProfile,
        ];
    }

    public function actionReloadEge(int $id)
    {
        $goBackUrl = Yii::$app->request->referrer ?: Yii::$app->homeUrl;
        if (Yii::$app->user->identity->isModer()) {
            $goBackUrl = Url::toRoute(['sandbox/moderate', 'id' => $id]);
        }

        $user = Yii::$app->user->identity;
        $this->entrantTestService->checkAccessibility($user, $id);
        $application = $this->entrantTestService->getApplication($id);

        if (!$application) {
            return $this->redirect($goBackUrl);
        }

        $this->entrantTestService->solvedConflict($application);

        Yii::$app->session->setFlash('successEgeReload', true);

        return $this->redirect($goBackUrl);
    }

    public function actionPrintApplicationByFullPackage(
        int $application_id,
        $report_type,
        string $application_build_type = BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL
    ) {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $application_id);
        $application = $this->bachelorSpecialityService->getApplication($application_id);

        if ($application === null) {
            throw new NotFoundHttpException('Не удалось найти нужное заявление');
        }
        $isSeparateStatementForFullPaymentBudget = $application->type->rawCampaign->separate_statement_for_full_payment_budget;
        if (!$isSeparateStatementForFullPaymentBudget) {
            $application_build_type = BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL;
        }

        [
            'fullFileName' => $fullFileName,
            'base64FileBinaryCode' => $base64FileBinaryCode,
        ] = $this->bachelorSpecialityService->getFileApplicationReport(
            $user,
            $application,
            $report_type,
            Yii::$app->soapClientWebApplication,
            $this->specialityPrioritiesService,
            $application_build_type
        );

        Yii::$app->response->sendContentAsFile($base64FileBinaryCode, $fullFileName);
    }

    public function actionPrintApplicationReturnForm(int $application_id, $type)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $application_id);
        $application = $this->bachelorSpecialityService->getApplication($application_id);

        if ($application === null) {
            throw new NotFoundHttpException('Не удалось найти нужное заявление');
        }

        $allow_types = [
            'ApplicationReturn',
            'AgreementReturn',
        ];
        if (!in_array($type, $allow_types)) {
            throw new InvalidArgumentException("Некорректный тип печатной формы.");
        }

        [
            'fullFileName' => $fullFileName,
            'base64FileBinaryCode' => $base64FileBinaryCode,
        ] = $this->bachelorSpecialityService->getFileApplicationReturnForm(
            $user,
            $application,
            $type,
            Yii::$app->soapClientWebApplication
        );

        Yii::$app->response->sendContentAsFile($base64FileBinaryCode, $fullFileName);
    }

    public function actionPrintEnrollmentRejectionForm(int $bachelor_spec_id)
    {
        $user = Yii::$app->user->identity;
        $bachelor_spec = $this->bachelorSpecialityService->getBachelorSpeciality($bachelor_spec_id);
        $application = $bachelor_spec->application;
        $this->bachelorSpecialityService->checkAccessibility($user, $application->id);

        [
            'fullFileName' => $fullFileName,
            'base64FileBinaryCode' => $base64FileBinaryCode,
        ] = $this->bachelorSpecialityService->getFileEnrollmentRejectionForm(
            $user,
            $application,
            Yii::$app->soapClientWebApplication,
            $bachelor_spec_id
        );

        Yii::$app->response->sendContentAsFile($base64FileBinaryCode, $fullFileName);
    }

    public function actionAccountingBenefits($id = null)
    {
        $user = Yii::$app->user->identity;
        $this->accountingBenefitsService->checkAccessibility($user, $id);
        $application = $this->accountingBenefitsService->getApplication($id);

        [
            'attachments' => $targetReceptionAttachments,
            'regulations' => $targetReceptionRegulations,
        ] = $this->targetReceptionsService->getRegulationsAndAttachmentsForTarget($application);
        [
            'attachments' => $olympAttachments,
            'regulations' => $olympRegulations,
        ] = $this->olympiadsService->getRegulationsAndAttachmentsForOlympiad($application);
        [
            'attachments' => $preferenceAttachments,
            'regulations' => $preferenceRegulations,
        ] = $this->benefitsService->getRegulationsAndAttachmentsForPreference($application);

        if (Yii::$app->request->isPost) {
            try {
                ['hasChanges' => $hasChanges] = $this->accountingBenefitsService->postProcessingRegulationsAndAttachments(
                    $application,
                    array_merge($targetReceptionAttachments, $olympAttachments, $preferenceAttachments),
                    array_merge($targetReceptionRegulations, $olympRegulations, $preferenceRegulations)
                );
            } catch (Throwable $th) {
                $this->accountingBenefitsService->processErrorMessageProcessingSavingAttachment($th, 'BachelorController.actionAccountingBenefits');
            }
            if (!$hasChanges) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                    'options' => ['class' => 'alert-warning']
                ]);
            } else if (!Yii::$app->user->identity->isModer()) {
                $nextStepUrl = $this->accountingBenefitsService->getNextStep($application);
                if ($nextStepUrl) {
                    return $this->redirect($nextStepUrl);
                }
            }
        }

        $resultTargets = $this->targetReceptionsService->getTargets($id);
        $resultBenefits = $this->benefitsService->getBenefits($id);
        $resultOlympiads = $this->olympiadsService->getOlympiads($id);

        return $this->render(
            'accounting-benefits',
            [
                'application' => $application,
                'application_comparison' => $this->accountingBenefitsService->getApplicationComparison($user, $application),
                'resultBenefits' => $resultBenefits,
                'resultOlympiads' => $resultOlympiads,
                'resultTargets' => $resultTargets,
                'targetReceptionRegulations' => $targetReceptionRegulations,
                'olympRegulations' => $olympRegulations,
                'preferenceRegulations' => $preferenceRegulations,
                'targetReceptionAttachments' => $targetReceptionAttachments,
                'olympAttachments' => $olympAttachments,
                'preferenceAttachments' => $preferenceAttachments,
                'targetReceptionsService' => $this->targetReceptionsService,
                'olympiadsService' => $this->olympiadsService,
                'benefitsService' => $this->benefitsService,
            ]
        );
    }

    public function actionDefineDisciplineSet(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->entrantTestService->checkAccessibility($user, $id);
        $application = $this->entrantTestService->getApplication($id);

        $hasError = false;
        if (Yii::$app->request->isPost) {
            [
                'hasError' => $hasError,
                'classForAlert' => $classForAlert,
                'messageForAlert' => $messageForAlert,
            ] = $this->entrantTestService->defineDisciplineSet($application, $user);


            Yii::$app->session->setFlash('alert', [
                'body' => $messageForAlert,
                'options' => ['class' => $classForAlert]
            ]);
        }

        $url = Yii::$app->request->referrer;
        if (!$hasError) {
            $url = Url::to(['bachelor/ege', 'id' => $id, '#' => 'bachelor_entrance_test_results']);

            if (Yii::$app->user->identity->isModer()) {
                $url = Url::to(['sandbox/moderate', 'id' => $id, '#' => 'forward']);
            }
        }
        return $this->redirect($url);
    }

    public function actionDeclineAgreement($id = null)
    {
        $user = Yii::$app->user->identity;
        $agreement_id = $id ?? (int)Yii::$app->request->post('agreement_id');
        if (!$agreement = AdmissionAgreement::findOne($agreement_id)) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl, 302);
        }

        $speciality = $agreement->speciality;
        $id = $speciality->application_id;
        $this->admissionAgreementService->checkAccessibility($user, $id);
        $application = $this->admissionAgreementService->getApplication($id);
        if (
            $speciality->is_enlisted ||
            !$application->canEdit() ||
            !$speciality->canAddAgreements()
        ) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl, 302);
        }

        try {
            $this->admissionAgreementService->declineAgreements($user, $application, $agreement);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash(
                'consentAddErrors',
                Yii::t('abiturient/bachelor/admission-agreement/all', 'Текст ошибки отзыве согласия на зачисление, на странице НП: `Возникли ошибки при отзыве согласия.`')
            );
        }

        return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl, 302);
    }

    public function actionRemoveAgreementDecline()
    {
        $user = Yii::$app->user->identity;
        $agreement_decline_id = (int)Yii::$app->request->post("agreement_decline_id");
        if (!$agreement_decline_id) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
        }

        
        
        $agreement_decline = AgreementDecline::findOne($agreement_decline_id);
        $agreement = $agreement_decline->agreement;
        $speciality = $agreement_decline->agreement->speciality;
        $applicationId = $speciality->application_id;

        $this->admissionAgreementService->checkAccessibility($user, $applicationId);
        $application = $this->admissionAgreementService->getApplication($applicationId);

        if ($agreement_decline->isSentTo1C) {
            throw new Exception('Невозможно отозвать отзыв согласия на зачисления, так как он уже одобрен');
        }

        $transcation = Yii::$app->db->beginTransaction();
        try {
            $agreement_to_delete = $agreement_decline->agreementToDelete;
            $agreement_to_delete->archive = true;
            $agreement_decline->archive();
            $agreement->status = AdmissionAgreement::STATUS_VERIFIED;

            if (!$agreement_to_delete->save(true, ['archive'])) {
                throw new RecordNotValid($agreement_to_delete);
            }

            if (!$agreement->save()) {
                throw new RecordNotValid($agreement);
            }

            $this->admissionAgreementService->changeAgreementDeclineHistoryProcess(
                $user,
                $application,
                $speciality,
                $agreement
            );

            $transcation->commit();
        } catch (Throwable $e) {
            $transcation->rollBack();
            throw $e;
        }

        return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
    }

    public function actionApplicationChangeHistory(
        int  $id,
        int  $sort_direction = SORT_ASC,
        ?int $limit = null,
        ?int $date_start = null,
        ?int $date_end = null
    ) {
        $user = Yii::$app->user->identity;
        $this->changeHistoryService->checkAccessibility($user, $id);
        $application = $this->changeHistoryService->getApplication($id);

        return $this->renderAjax(
            'ajax/_applicationChangeHistory',
            ['change_history' => $this
                ->changeHistoryService
                ->getChangeHistoryByApplicationWithFilters(
                    $user,
                    $application,
                    $sort_direction,
                    $date_start,
                    $date_end,
                    $limit
                )]
        );
    }

    public function actionApplicationInfiniteScrollHistory(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $this->changeHistoryService->checkAccessibility($user, $id);
        $application = $this->changeHistoryService->getApplication($id);

        $changeHistories = $this
            ->changeHistoryService
            ->getChangeHistoryByApplicationWithFiltersFromPost(
                $user,
                $application
            );
        $appendRender = '';
        foreach ($changeHistories as $historyRow) {
            $appendRender .= $this->renderAjax(
                'ajax/_applicationChangeHistoryOneNode',
                ['historyRow' => $historyRow]
            );
        }

        return ['appendRender' => $appendRender];
    }

    public function actionUpdateFullPackage(int $id, string $baseUrl)
    {
        $user = Yii::$app->user->identity;
        $this->application1CProcessorService->checkAccessibility($user, $id);
        $application = $this->application1CProcessorService->getApplication($id);

        if (empty($application)) {
            throw new NotFoundHttpException("Не удалось найти указанное заявление");
        }
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $application = $this->application1CProcessorService->updateApplication($application);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect(Url::to([$baseUrl, 'id' => $application->id]));
    }

    public function actionMakeCopy(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->application1CProcessorService->checkAccessibility($user, $id);
        $application = $this->application1CProcessorService->getApplication($id);

        if (!$application) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
        }
        [
            'url' => $url,
            'error_alert' => $errorAlert
        ] = $this->application1CProcessorService->createApplicationCopy($user, $application);

        if ($errorAlert) {
            Yii::$app->session->setFlash('alert', [
                'body' => $errorAlert,
                'options' => ['class' => 'alert-danger'],
            ]);
        }
        if ($url) {
            return $this->redirect($url);
        }

        return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
    }

    public function actionValidateApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->application1CProcessorService->checkAccessibility($user, $id);
        $application = $this->application1CProcessorService->getApplication($id);

        return $this->asJson((new CheckAllApplication())->handleSentToModerateApplicationCheck($application));
    }

    public function actionCheckCanSendApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->individualAchievementService->checkAccessibility($user, $id);
        $application = $this->individualAchievementService->getApplication($id);

        $result = $user->canMakeStep('send-application', $application);

        return $this->asJson($result ? 'true' : 'false');
    }

    public function actionSaveAttachedApplicationFiles(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->bachelorSpecialityService->checkAccessibility($user, $id);
        $application = $this->bachelorSpecialityService->getApplication($id);

        [
            'attachments' => $attachments,
            'regulations' => $regulations,
        ] = $this->bachelorSpecialityService->getRegulationsAndAttachmentsForEducation($application);

        if (!Yii::$app->request->isPost) {
            return $this->redirect(Url::to(['/bachelor/application', 'id' => $application->id]));
        }

        try {
            [
                'hasChanges' => $hasChanges,
                'attachments' => $attachments,
                'regulations' => $regulations,
            ] = $this->bachelorSpecialityService->postProcessingRegulationsAndAttachments(
                $application,
                $attachments,
                $regulations
            );
        } catch (Throwable $th) {
            $this->bachelorSpecialityService->processErrorMessageProcessingSavingAttachment($th, 'BachelorController.actionSaveAttachedApplicationFiles');
        }

        if (!$hasChanges && !$user->isModer()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null),
                'options' => ['class' => 'alert-warning']
            ]);
        } else if (!$user->isModer()) {
            $nextStepUrl = $this->bachelorSpecialityService->getNextStep($application);
            if ($nextStepUrl) {
                return $this->redirect($nextStepUrl);
            }
        }

        return $this->redirect(Url::to(['/bachelor/application', 'id' => $application->id]));
    }
}
