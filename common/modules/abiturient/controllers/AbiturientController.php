<?php

namespace common\modules\abiturient\controllers;

use common\commands\command\AddToTimelineCommand;
use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\behaviors\emailConfirmBehavior\EmailConfirmBehavior;
use common\models\dictionary\Fias;
use common\models\dictionary\FiasDoma;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\IndividualAchievementDocumentType;
use common\models\settings\SandboxSetting;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ActualAddressData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\PersonalData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\services\abiturientController\bachelor\Application1CProcessorService;
use common\services\abiturientController\bachelor\ApplicationsService;
use common\services\abiturientController\bachelor\IndividualAchievementService;
use common\services\abiturientController\questionary\AddressDataService;
use common\services\abiturientController\questionary\AvatarService;
use common\services\abiturientController\questionary\InitializationQuestionaryService;
use common\services\abiturientController\questionary\ParentDataService;
use common\services\abiturientController\questionary\PassportDataService;
use common\services\abiturientController\questionary\QuestionaryService;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\bootstrap4\Html;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\NotFoundHttpException;
use yii\web\Response;




class AbiturientController extends Controller
{
    
    private IndividualAchievementService $individualAchievementService;

    
    private QuestionaryService $questionaryService;

    
    private PassportDataService $passportDataService;

    
    private AvatarService $avatarService;

    
    private ApplicationsService $applicationsService;

    
    private AddressDataService $addressDataService;

    
    private InitializationQuestionaryService $initializationQuestionaryService;

    
    private ParentDataService $parentDataService;

    
    private Application1CProcessorService $application1CProcessorService;

    













    public function __construct(
        $id,
        $module,
        AvatarService                    $avatarService,
        ParentDataService                $parentDataService,
        AddressDataService               $addressDataService,
        QuestionaryService               $questionaryService,
        ApplicationsService              $applicationsService,
        PassportDataService              $passportDataService,
        IndividualAchievementService     $individualAchievementService,
        InitializationQuestionaryService $initializationQuestionaryService,
        Application1CProcessorService    $application1CProcessorService,
        $config = []
    ) {
        $this->avatarService                    = $avatarService;
        $this->parentDataService                = $parentDataService;
        $this->addressDataService               = $addressDataService;
        $this->questionaryService               = $questionaryService;
        $this->applicationsService              = $applicationsService;
        $this->passportDataService              = $passportDataService;
        $this->individualAchievementService     = $individualAchievementService;
        $this->initializationQuestionaryService = $initializationQuestionaryService;
        $this->application1CProcessorService    = $application1CProcessorService;

        parent::__construct($id, $module, $config);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@common/modules/abiturient/views/abiturient');
    }

    public function behaviors()
    {
        $sandboxEnabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);
        if ($sandboxEnabled == null) {
            $sandboxEnabled = 0;
        }
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'applications',
                            'area',
                            'city',
                            'delete-parent-data',
                            'delete-passport',
                            'have-no-previous-passport',
                            'ia-doc-types',
                            'ialist',
                            'index',
                            'mark-return-application',
                            'mark-reject-enrollment',
                            'parent-form',
                            'postalindex',
                            'questionary',
                            'region',
                            'remove-application',
                            'set-parent-data',
                            'set-passport',
                            'street',
                            'test',
                            'update-contact',
                            'update',
                            'village',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_ABITURIENT]
                    ],
                    [
                        'actions' => [
                            'addia',
                            'delete-photo',
                            'get-education-by-document-type',
                            'ia-file-required',
                            'passport-grid',
                            'passport-modals',
                            'upload-photo',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_ABITURIENT, User::ROLE_MANAGER]
                    ],
                    [
                        'actions' => [
                            'area',
                            'city',
                            'ia-doc-types',
                            'postalindex',
                            'region',
                            'street',
                            'village',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ],
                    [
                        'actions' => ['return-application', 'reject-enrollment'],
                        'allow' => $sandboxEnabled->value == 1,
                        'roles' => [User::ROLE_MANAGER]
                    ],
                    [
                        'actions' => ['return-application', 'reject-enrollment'],
                        'allow' => $sandboxEnabled->value == 0,
                        'roles' => [User::ROLE_ABITURIENT]
                    ]
                ],

                'denyCallback' => function () {
                    $this->redirect('/');
                }
            ],
            [
                'class' => EmailConfirmBehavior::class,
                'user' => Yii::$app->user->identity
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'passport-grid' => ['GET'],
                    'passport-modals' => ['GET'],
                    'set-passport' => ['POST']
                ],
            ],
        ];
    }

    public function actions()
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function actionIndex()
    {
        $timeZoneError = $this->initializationQuestionaryService->checkTimeZone();

        $user = Yii::$app->user->identity;
        if ($user->canViewStep('my-applications') && !$timeZoneError) {
            return $this->redirect('/abiturient/applications', 302);
        }
        $user->syncApplicationsAndQuestionaryWith1C();

        $campaignStartDates = [];
        $abiturientQuestionary = new AbiturientQuestionary();
        $canCreateQuestionary = $abiturientQuestionary->canCreateQuestionary();
        if (!$canCreateQuestionary) {
            $campaignStartDates = $abiturientQuestionary->getCampaignsStartDates();
        }
        return $this->render(
            'index',
            [
                'timeZoneError' => $timeZoneError,
                'campaignStartDates' => $campaignStartDates,
                'canCreateQuestionary' => $canCreateQuestionary,
            ]
        );
    }

    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;
        
        if ($user->abiturientQuestionary && !$user->abiturientQuestionary->getLinkedBachelorApplication()->exists()) {
            AbiturientQuestionary::UpdateDataFromOneS($user->abiturientQuestionary);
        }
        return $this->redirect('/abiturient/questionary', 302);
    }

    public function actionQuestionary(int $app_id = null)
    {
        $user = Yii::$app->user->identity;
        $this->questionaryService->checkAccessibilityToRelatedBachelorApplication($user, $app_id);
        $current_application = $this->questionaryService->getRelatedBachelorApplication($user, $app_id);

        [
            'questionary' => $questionary,
            'needRefreshPage' => $needRefreshPage,
        ] = $this->questionaryService->getQuestionary($user);
        if ($needRefreshPage) {
            return $this->redirect('/abiturient/questionary', 302);
        }

        if (!$user->canMakeStep('questionary', $current_application)) {
            return $this->redirect('/abiturient/index', 302);
        }

        $request = Yii::$app->request;
        $regulations = $this->questionaryService->getRegulations($user, $questionary);
        $edit_block_reasons = [];

        $canEdit = !$questionary || $questionary->canEditQuestionary();
        if (
            $canEdit &&
            $questionary &&
            AbiturientQuestionary::isBlockedAfterApprove($questionary)
        ) {
            $edit_block_reasons = [
                Yii::t(
                    'abiturient/questionary/all',
                    'Текст ошибки с предупреждением о блокировке анкеты на редактирование на странице анкеты поступающего: `Запрещено редактирование анкеты после проверки модератором`'
                )
            ];
            $canEdit = false;
        }
        $personal_data = $this->questionaryService->getQuestionnaireDependentModels($questionary, 'personalData', PersonalData::class);
        $address_data = $this->questionaryService->getQuestionnaireDependentModels($questionary, 'addressData', AddressData::class);
        $actualAddressData = $this->questionaryService->getQuestionnaireDependentModels($questionary, 'actualAddressData', ActualAddressData::class);
        $actualAddressData->validation_extender ? $actualAddressData->validation_extender->modelPreparationCallback() : null;
        $abitAvatar = $questionary->getComputedAbiturientAvatar();
        $attachments = $this->questionaryService->getAttachments($questionary);

        if ($request->isPost) {
            $anyAttachmentChanged = $this->questionaryService->saveRegulationsOrAttachmentsWithChangesReturned($questionary, $regulations, $attachments, $canEdit);

            if ($canEdit) {
                [
                    'validated' => $validated,
                    'isSaved' => $isSaved,
                    'anyDataChanged' => $anyDataChanged,
                ] = $this->questionaryService->processFromPost(
                    $user,
                    $questionary,
                    $personal_data,
                    $address_data,
                    $actualAddressData
                );

                $anyDataChanged = $anyDataChanged || $anyAttachmentChanged;
                if ($validated) {
                    if ($anyDataChanged) {
                        $user->resetApplicationStatuses();
                    } else {
                        Yii::$app->session->setFlash('alert', [
                            'body' => Yii::$app->configurationManager->getText('no_data_saved_text', ArrayHelper::getValue($user, 'applications.0.type')),
                            'options' => ['class' => 'alert-warning']
                        ]);
                    }

                    Yii::$app->session->setFlash('questionaryIsSaved', $isSaved);
                    return $this->redirect('/abiturient/questionary', 302);
                }
            }
        }

        [
            'attachmentErrors' => $attachmentErrors,
            'isAttachmentsAdded' => $isAttachmentsAdded,
        ] = $this->questionaryService->checkAttachmentFiles($questionary, $canEdit);

        $hasApplicationType = ApplicationType::find()->active()->exists();

        $confirmEmail = Yii::$app->configurationManager->getCode('confirm-email');
        $canChangeFio = QuestionarySettings::getSettingByName('can_change_fio_after_first_application');
        $canChangePassport = QuestionarySettings::getSettingByName('can_change_passport_after_first_application');

        return $this->render(
            'questionary',
            [
                'questionary_comparison' => $this->questionaryService->getQuestionaryComparison($user, $questionary),
                'questionary' => $questionary,
                'personal_data' => $personal_data,
                'current_application' => $current_application,
                'address_data' => $address_data,
                'actual_address_data' => $actualAddressData,
                'passports' => new ActiveDataProvider([
                    'query' => $questionary->getPassportData()
                ]),
                'parents' => new ActiveDataProvider([
                    'query' => $questionary->getParentData()
                ]),
                'edit_block_reasons' => $edit_block_reasons,
                'abitAvatar' => $abitAvatar,
                'attachments' => $attachments,
                'regulations' => $regulations,
                'isAttachmentsAdded' => $isAttachmentsAdded,
                'attachmentErrors' => $attachmentErrors,
                'isPost' => Yii::$app->request->isPost,
                'hasApplicationType' => $hasApplicationType,
                'canEdit' => $canEdit,
                'confirmEmail' => $confirmEmail === '1',
                'canChangeFio' => ($canChangeFio && !$questionary->isNotCreatedDraft()) || $canEdit,
                'canChangePassport' => ($canChangePassport && !$questionary->isNotCreatedDraft()) || $canEdit,
            ]
        );
    }

    public function actionDeletePassport()
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $user = Yii::$app->user->identity;
        $questionary = $user->abiturientQuestionary;

        $this->passportDataService->deletePassportData($user, $questionary);
        return $this->renderAjax(
            'questionary_partial/_document_grid',
            $this->passportDataService->renderPassports($questionary)
        );
    }

    


    public function actionSetPassport()
    {
        if (Yii::$app->request->isPost) {
            $user = Yii::$app->user->identity;
            $questionary = $user->abiturientQuestionary;

            $this->passportDataService->setPassportData($user, $questionary);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(Yii::$app->request->referrer ?? ['/abiturient/questionary']);
        } else {
            return $this->asJson(['status' => true, 'messages' => []]);
        }
    }

    public function actionPassportGrid($questionary_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $questionary = AbiturientQuestionary::findOne($questionary_id);

        return $this->renderAjax(
            'questionary_partial/_document_grid',
            $this->passportDataService->renderPassports($questionary)
        );
    }

    public function actionPassportModals(int $questionary_id, ?int $app_id = null)
    {
        $user = Yii::$app->user->identity;
        $this->passportDataService->checkAccessibility($user, $questionary_id);

        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $questionary = AbiturientQuestionary::findOne($questionary_id);
        $passports = new ActiveDataProvider([
            'query' => $questionary->getPassportData()
        ]);

        $action = $user->isModer() ?
            '/sandbox/set-passport' : '/abiturient/set-passport';

        $related_application = null;
        if ($questionary->user) {
            $related_application = $this->questionaryService->getRelatedBachelorApplication($questionary->user, $app_id);
        }

        return $this->renderAjax('questionary_partial/_passportModals', [
            'isReadonly' => !$questionary->canEditQuestionary(),
            'passports' => $passports,
            'action' => $action,
            'application' => $related_application,
        ]);
    }

    public function actionDeleteParentData()
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $user = Yii::$app->user->identity;
        $id = ArrayHelper::getValue($this->request->post(), 'parentDataId');

        $parentData = ParentData::findOne($id);
        if ($parentData != null) {
            $this->parentDataService->checkAccessibility($user, $parentData->questionary_id);

            $parentData->archive();
        }

        $this->parentDataService->parentDataChangedEvent($user);
        return $this->renderParents($user->abiturientQuestionary);
    }


    public function actionSetParentData()
    {
        if (!Yii::$app->request->isAjax) {
            throw new UserException('Is not ajax');
        }

        $user = Yii::$app->user->identity;
        $questionary = $user->abiturientQuestionary;

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

    public function actionApplications()
    {
        $user = Yii::$app->user->identity;
        if (!$user->canMakeStep('my-applications')) {
            return $this->redirect('/abiturient/index', 302);
        }

        return $this->render(
            'applications',
            ['applications' => $this->applicationsService->getApplications($user)]
        );
    }

    






    public function actionRemoveApplication()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect('/abiturient/applications', 302);
        }
        $appId = (int) Yii::$app->request->post('appid');

        $user = Yii::$app->user->identity;
        $this->applicationsService->checkAccessibility($user, $appId);
        $application = $this->applicationsService->getApplication($appId);
        if (!$application) {
            return $this->redirect('/abiturient/applications');
        }

        $this->applicationsService->archiveApplications($user, $application);

        return $this->redirect('/abiturient/applications', 302);
    }

    public function actionRegion()
    {
        $regions = $this->addressDataService->getAllRegions();
        $results = $this->addressDataService->formattingDataForSelector($regions, "{{name}} {{short}}");

        return $this->asJson(['results' => $results, 'selected' => '', 'pagination' => ['more' => !empty($regions)]]);
    }

    public function actionArea()
    {
        ['parents' => $parents] = $this->addressDataService->getDepDropParamsFromPost();

        if (
            EmptyCheck::isEmpty($parents) ||
            EmptyCheck::isLoadingStringOrEmpty($parents[0])
        ) {
            return $this->asJson(['results' => '', 'selected' => '']);
        }

        $areas = $this->addressDataService->getAllAreas($parents[0]);
        $results = $this->addressDataService->formattingDataForSelector($areas);
        return $this->asJson(['results' => $results, 'selected' => '', 'pagination' => ['more' => !empty($areas)]]);
    }

    public function actionCity()
    {
        [
            'params' => $params,   
            'parents' => $parents, 
        ] = $this->addressDataService->getDepDropParamsFromPost();

        if (
            EmptyCheck::isEmpty($params) ||
            EmptyCheck::isLoadingStringOrEmpty($params[0])
        ) {
            return $this->asJson(['results' => '', 'selected' => '']);
        }

        $cities = $this->addressDataService->getAllCities($params[0], $parents[0]);
        $results = $this->addressDataService->formattingDataForSelector($cities);

        return $this->asJson(['results' => $results, 'selected' => '', 'pagination' => ['more' => !empty($cities)]]);
    }

    public function actionVillage()
    {
        [
            'params' => $params,   
            'parents' => $parents, 
        ] = $this->addressDataService->getDepDropParamsFromPost();

        if (
            EmptyCheck::isEmpty($params) ||
            EmptyCheck::isLoadingStringOrEmpty($params[0])
        ) {
            return $this->asJson(['results' => '', 'selected' => '']);
        }

        $villages = $this->addressDataService->getAllVillages($params[0], $parents[0], $parents[1]);
        $template = '{{name}}';
        if (EmptyCheck::isLoadingStringOrEmpty($parents[0])) {
            $template = '{{name}} ({{areaName}} {{areaShort}})';
        }
        $results = $this->addressDataService->formattingDataForSelector($villages, $template);
        return $this->asJson(['results' => $results, 'selected' => '', 'pagination' => ['more' => !empty($villages)]]);
    }

    public function actionStreet()
    {
        [
            'params' => $params,   
            'parents' => $parents, 
        ] = $this->addressDataService->getDepDropParamsFromPost();

        if (
            EmptyCheck::isEmpty($params) ||
            EmptyCheck::isLoadingStringOrEmpty($params[0])
        ) {
            return $this->asJson(['results' => '', 'selected' => '']);
        }

        $streets = $this->addressDataService->getAllStreets($params[0], $params[1], $parents[0], $parents[1]);
        $results = $this->addressDataService->formattingDataForSelector($streets, "{{name}} {{short}}");

        return $this->asJson(['results' => $results, 'selected' => '', 'pagination' => ['more' => !empty($streets)]]);
    }

    public function actionPostalindex()
    {
        $sid = ArrayHelper::getValue(Yii::$app->request->post(), 'sid', '');
        if (!$sid) {
            return '';
        }
        $fiasExists = Fias::find()
            ->andWhere(['code' => $sid])
            ->exists();

        if (!$fiasExists) {
            return '';
        }

        $house = ArrayHelper::getValue(Yii::$app->request->post(), 'house', '');
        $housing = ArrayHelper::getValue(Yii::$app->request->post(), 'housing', '');

        return FiasDoma::streetIndex($sid, $house, $housing);
    }

    public function actionIalist(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->individualAchievementService->checkAccessibility($user, $id);
        $application = $this->individualAchievementService->getApplication($id);
        $application->archiveAdmissionCampaignHandler->handle();

        if (!$this->individualAchievementService->checkIfAbiturientQuestionaryFilled($application)) {
            Yii::$app->session->setFlash('needToSaveQuestionary', 'true');
            return $this->redirect('/abiturient/questionary', 302);
        }

        if (!$user->canMakeStep('ia-list', $application)) {
            return $this->redirect('/abiturient/index', 302);
        }
        $ind_achs = new ActiveDataProvider([
            'query' => $application->getIndividualAchievements()
        ]);
        $hasError = false;
        $error = Yii::$app->session->get('ia_error');

        if ($error == '1') {
            $hasError = true;
            Yii::$app->session->set('ia_error', '0');
        }

        return $this->render(
            'ialist',
            [
                'application_comparison' => $this->individualAchievementService->getApplicationComparison($user, $application),
                'ind_achs' => $ind_achs,
                'application' => $application,
                'canEdit' => ($application->canEdit() && $application->canEditSpecialities()),
                'hasError' => $hasError,
                'user' => $user
            ]
        );
    }

    public function actionIaFileRequired()
    {
        $document_type_id = Yii::$app->request->post('document_type_id');
        if (!is_null($document_type_id)) {
            $type = IndividualAchievementDocumentType::findOne($document_type_id);
            if (!empty($type)) {
                return $this->asJson((bool)$type->scan_required);
            }
        }
        return $this->asJson(false);
    }

    public function actionIaDocTypes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->individualAchievementService->getIndividualAchievementDocumentTypesDataForSelect();
    }

    public function actionGetEducationByDocumentType(int $app_id, int $ia_document_type_id)
    {
        
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $this->individualAchievementService->checkAccessibility($user, $app_id);
        $ia_document_type = IndividualAchievementDocumentType::findOne($ia_document_type_id);
        if (is_null($ia_document_type)) {
            throw new NotFoundHttpException('Тип документа не найден');
        }
        $document_type = $ia_document_type->documentTypeRef;
        $application = $this->individualAchievementService->getApplication($app_id);

        $education = $application->getEducations()
            ->joinWith(['documentType document_type'])
            ->andWhere(['document_type.ref_key' => $document_type->ref_key])
            ->one();
        return [
            'matched' => $education ? [
                'id' => $education->id,
                'series' => $education->series,
                'number' => $education->number,
                'document_type_id' => $education->document_type_id,
                'issued_by' => $education->getSchoolName(),
                'issued_at' => $education->date_given,
                'contractor_id' => $education->contractor_id,
                'files' => $education->getAttachments()
                    ->joinWith(['linkedFile linked_file'])
                    ->select(['linked_file.upload_name'])
                    ->column()
            ] : null
        ];
    }

    public function actionAddia(int $app_id, $id = null)
    {
        $user = Yii::$app->user->identity;
        $this->individualAchievementService->checkAccessibility($user, $app_id);

        $application = $this->individualAchievementService->getApplication($app_id);

        if (!Yii::$app->request->isPost) {
            return $this->redirect(Yii::$app->request->referrer);
        }
        $ia = $this->individualAchievementService->getOrCrateIndividualAchievement(
            $user,
            $application,
            $id
        );

        if ($this->individualAchievementService->fillFromEducationData($application, $ia)) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        $transaction = Yii::$app->db->beginTransaction();
        if ($transaction === null) {
            throw new UserException('Невозможно создать транзакцию.');
        }

        try {
            $hasChangedAttributes = $this->individualAchievementService->savingProcess($application, $ia);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $alertBody = Yii::$app->configurationManager->getText('no_data_saved_text');
        $alertClass = 'alert-warning';
        if ($hasChangedAttributes) {
            $alertBody = Yii::t(
                'abiturient/bachelor/individual-achievement/all',
                'Текст сообщения успешного сохранения формы на стр. индивидуальных достижений: `Форма сохранена успешно.`'
            );
            $alertClass = 'alert-success';
        }

        Yii::$app->session->setFlash('alert', [
            'body' => $alertBody,
            'options' => ['class' => $alertClass]
        ]);

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUpdateContact()
    {
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->isPost && $user->userRef) {
            $this->questionaryService->updateContactFromPost($user);
        }
        return $this->redirect(Url::toRoute(['/abiturient/questionary']), 302);
    }

    public function actionReturnApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->applicationsService->checkAccessibility($user, $id);
        $application = $this->applicationsService->getApplication($id);

        $sandboxEnabled = Yii::$app->configurationManager->getSandboxEnabled();
        if (
            !$application ||
            ($sandboxEnabled && !$user->isModer()) ||
            (!$sandboxEnabled && $application->user_id != $user->id)
        ) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
        }

        $errorMessage = $this->applicationsService->returnApplication(
            $application,
            $sandboxEnabled
        );
        if (!$errorMessage) {
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'category' => 'abiturient',
                'event' => 'application_return',
                'data' => [
                    'public_identity' => $user->getPublicIdentity(),
                    'user_id' => $user->getId(),
                    'campaign' => $application->type->campaignName,
                ]
            ]));
        }
        $url = '/abiturient/applications';
        if ($sandboxEnabled && $user->isModer()) {
            $url = Url::toRoute(['/sandbox/index']);
            if ($errorMessage) {
                $url = Url::toRoute(['/sandbox/moderate', 'id' => $application->id]);
                $returnApplicationStepsInfo = [
                    'status' => ApplicationApplyingStep::STEP_STATUS_FAILED,
                    'errors' => [$errorMessage],
                    'name' => Yii::t(
                        'abiturient/applications/all',
                        'Тело ошибки отзыва заявление; на странице заявлений поступающего: `Возникли ошибки.`'
                    ),
                    'shortName' => 'return-application-steps',
                    'statusMessage' => Yii::t(
                        'abiturient/applications/all',
                        'Тело ошибки отзыва заявление; на странице заявлений поступающего: `Во время отзыва заявления.`'
                    ),
                ];
                Yii::$app->session->setFlash('returnApplicationStepsInfo', $returnApplicationStepsInfo);
            }
        }
        return $this->redirect($url);
    }

    public function actionMarkReturnApplication(int $id)
    {
        $user = Yii::$app->user->identity;
        $this->applicationsService->checkAccessibility($user, $id);
        $application = $this->applicationsService->getApplication($id);

        if ($application->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('abiturient/errors', 'Сообщение поступающему при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления`'),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/abiturient/applications']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->applicationsService->markReturnApplication($user, $application);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $this->redirect('/abiturient/applications');
    }

    public function actionMarkRejectEnrollment(int $bachelor_spec_id)
    {
        $bachelor_spec = $this->applicationsService->getBachelorSpeciality($bachelor_spec_id);
        $application = $bachelor_spec->application;
        $user = Yii::$app->user->identity;

        $this->applicationsService->checkAccessibility($user, $application->id);

        if ($application->isArchive()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('abiturient/errors', 'Сообщение поступающему при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления`'),
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/abiturient/applications']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $application_to_send = $this->applicationsService->markRejectEnrollment($user, $bachelor_spec);
            $this->application1CProcessorService->sendApplicationTo1C($user, $application_to_send);
            
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $this->redirect('/abiturient/applications');
    }

    public function actionRejectEnrollment(int $bachelor_spec_id)
    {
        $bachelor_spec = $this->applicationsService->getBachelorSpeciality($bachelor_spec_id);
        $application = $bachelor_spec->application;
        $user = Yii::$app->user->identity;

        $this->applicationsService->checkAccessibility($user, $application->id);

        $sandboxEnabled = Yii::$app->configurationManager->getSandboxEnabled();
        if (
            !$application ||
            ($sandboxEnabled && !$user->isModer()) ||
            (!$sandboxEnabled && $application->user_id != $user->id)
        ) {
            return $this->redirect(Yii::$app->request->referrer ?? Yii::$app->homeUrl);
        }

        $errorMessage = $this->applicationsService->rejectEnrollment(
            $user,
            $bachelor_spec,
            $sandboxEnabled
        );
        
        if (!$errorMessage) {
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'category' => 'abiturient',
                'event' => 'enrollment_rejection',
                'data' => [
                    'public_identity' => $user->getPublicIdentity(),
                    'user_id' => $user->getId(),
                    'campaign' => $application->type->campaignName,
                ]
            ]));
        }

        $url = '/abiturient/applications';
        if ($sandboxEnabled && $user->isModer()) {
            $url = Url::toRoute(['/sandbox/index']);
            if ($errorMessage) {
                $url = Url::toRoute(['/sandbox/moderate', 'id' => $application->id]);
                $returnApplicationStepsInfo = [
                    'status' => ApplicationApplyingStep::STEP_STATUS_FAILED,
                    'errors' => [$errorMessage],
                    'name' => Yii::t(
                        'abiturient/applications/all',
                        'Тело ошибки отзыва заявление; на странице заявлений поступающего: `Возникли ошибки.`'
                    ),
                    'shortName' => 'return-application-steps',
                    'statusMessage' => Yii::t(
                        'abiturient/applications/all',
                        'Тело ошибки отзыва заявление; на странице заявлений поступающего: `Во время отказа от зачисления.`'
                    ),
                ];
                Yii::$app->session->setFlash('returnApplicationStepsInfo', $returnApplicationStepsInfo);
            }
        }
        return $this->redirect($url);
    }

    private function renderParents(AbiturientQuestionary $questionary)
    {
        return $this->renderAjax("questionary_partial/parentData/_parent_grid", [
            'parents' => new ActiveDataProvider([
                'query' => $questionary->getParentData()
            ]),
            'canEdit' => $questionary->canEditQuestionary(),
        ]);
    }

    public function actionParentForm(?int $current_application_id = null)
    {
        $user = Yii::$app->user->identity;
        $this->questionaryService->checkAccessibilityToRelatedBachelorApplication($user, $current_application_id);
        $current_application = $this->questionaryService->getRelatedBachelorApplication($user, $current_application_id);

        [
            'questionary' => $questionary,
            'needRefreshPage' => $_,
        ] = $this->questionaryService->getQuestionary($user);

        $model = $this->parentDataService->getOrBuildParentData(
            $user,
            $questionary,
            'parentDataId'
        );
        $canEdit = $questionary->canEditQuestionary();

        $action = Url::to(array_merge(
            ['/abiturient/set-parent-data'],
            $model->id ? ['id' => $model->id] : []
        ));

        return $this->renderAjax(
            'questionary_partial/parentData/_parentForm',
            [
                'model' => $model,
                'familyTypes' => $this->parentDataService->getFamilyTypes(),
                'passportTypes' => $this->parentDataService->getAllIdentityDocuments(),
                'document_type' => $this->parentDataService->getDocumentTypeID(),
                'keynum' => $model->id,
                'action' => $action,
                'canEdit' => $canEdit,
                'isReadonly' => !$canEdit,
                'application' => $current_application,
            ]
        );
    }

    public function actionUploadPhoto(int $questionary_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = Yii::$app->user->identity;

        $this->avatarService->checkAccessibility($user, $questionary_id);
        $questionary = $this->avatarService->getQuestionaryById($user, $questionary_id);

        $error = '';
        $status = false;
        $fileLink = '';
        $transaction = Yii::$app->db->beginTransaction();
        try {
            [
                'error' => $error,
                'status' => $status,
                'fileLink' => $fileLink,
            ] = $this->avatarService->uploadAvatar($questionary);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            $status = false;
            $error = 'Возникла критическая ошибка обработки фотографии.';

            Yii::error(
                "Возникла критическая ошибка обработки фотографии. По причине: {$e->getMessage()}",
                'AbiturientController.actionUploadPhoto'
            );
        }
        return [
            'error' => $error,
            'status' => $status,
            'filelink' => $fileLink,
        ];
    }

    public function actionDeletePhoto(int $questionary_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = Yii::$app->user->identity;

        $this->avatarService->checkAccessibility($user, $questionary_id);
        $questionary = $this->avatarService->getQuestionaryById($user, $questionary_id);

        try {
            return ['status' => $this->avatarService->deleteAvatar($questionary)];
        } catch (Throwable $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionHaveNoPreviousPassport(int $id)
    {
        $user = Yii::$app->user->identity;
        $questionary = $this->passportDataService->getQuestionaryById($user, $id);

        $questionary->have_no_previous_passport = true;
        if (!$questionary->save()) {
            throw new RecordNotValid($questionary);
        }

        Yii::$app->session->setFlash('alert', [
            'body' => Yii::t(
                'abiturient/questionary/all',
                'Текст при успешном сохранении пометки об отсутствии предыдущего паспорта: `Информация успешно сохранена, можете вернуться к подаче заявления.`'
            ),
            'options' => ['class' => 'alert-success']
        ]);

        return $this->redirect(['/abiturient/questionary']);
    }
}
