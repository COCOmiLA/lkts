<?php

namespace frontend\controllers;

use backend\models\DocumentTemplate;
use backend\models\MainPageInstructionFile;
use backend\models\MainPageSetting;
use common\actions\SetLocaleAction;
use common\components\filesystem\FilterFilename;
use common\components\soapException;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\Attachment;
use common\models\AttachmentTypeTemplate;
use common\models\dictionary\DocumentType;
use common\models\dictionary\DocumentTypePropertiesSetting;
use common\models\EmptyCheck;
use common\models\Regulation;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\PrintForm;
use common\services\abiturientController\bachelor\accounting_benefits\BenefitsService;
use common\services\abiturientController\bachelor\accounting_benefits\OlympiadsService;
use common\services\abiturientController\bachelor\accounting_benefits\TargetReceptionsService;
use common\services\abiturientController\bachelor\LoadScansService;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;




class SiteController extends Controller
{
    
    private BenefitsService $benefitsService;

    
    private OlympiadsService $olympiadsService;

    
    private TargetReceptionsService $targetReceptionsService;

    
    private LoadScansService $loadScansService;

    








    public function __construct(
        $id,
        $module,
        BenefitsService $benefitsService,
        LoadScansService $loadScansService,
        OlympiadsService $olympiadsService,
        TargetReceptionsService $targetReceptionsService,
        $config = []
    ) {
        $this->benefitsService = $benefitsService;
        $this->loadScansService = $loadScansService;
        $this->olympiadsService = $olympiadsService;
        $this->targetReceptionsService = $targetReceptionsService;

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
                            'authorize',
                            'download-regulation-file',
                            'error',
                            'set-locale',
                        ],
                        'allow' => true,
                        'roles' => ['?', '@']
                    ],
                    [
                        'actions' => [
                            'academicplan',
                            'accounting-benefits',
                            'accounting-olympiads',
                            'chat',
                            'delete-benefits',
                            'delete-target',
                            'deletefile',
                            'deleteia',
                            'doc-type-olympiads',
                            'doc-type',
                            'download-agreement-decline',
                            'download-attachment-type-template',
                            'download-benefits',
                            'download-form',
                            'download-instruction-attachment',
                            'download-paid-contract',
                            'download-target',
                            'download-template',
                            'download',
                            'downloadagreement',
                            'downloadia',
                            'edit-benefits',
                            'edit-olympiads',
                            'edit-target',
                            'filter-olympiads',
                            'grade',
                            'index',
                            'olymp-type',
                            'portfolio',
                            'schedule',
                            'selected-tabular-element',
                            'tabular',
                            'target-reception',
                        ],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'actions' => [
                            'document-type-rules',
                        ],
                        'allow' => true,
                        'roles' => ['@', '?']
                    ]
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ],
            'set-locale' => [
                'class' => SetLocaleAction::class,
                'languages' => Yii::$app->localizationManager->getAvailableLocales()
            ]
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->can(User::ROLE_ADMINISTRATOR)) {
            return $this->redirect('/admin', 302);
        } elseif (Yii::$app->user->can(User::ROLE_MANAGER) || Yii::$app->user->can(User::ROLE_VIEWER)) {
            return $this->redirect('/sandbox/index', 302);
        } elseif (Yii::$app->user->can(User::ROLE_STUDENT)) {
            return $this->redirect('/student/index', 302);
        } elseif (Yii::$app->user->can(User::ROLE_TEACHER)) {
            return $this->redirect('/teacher/index', 302);
        } elseif (Yii::$app->user->can(User::ROLE_ABITURIENT)) {
            return $this->redirect('/abiturient/index', 302);
        } else {
            return $this->redirect(Url::toRoute(['user/sign-in/userset']), 302);
        }
    }

    







    public function actionTabular()
    {
        $post_buffer = Yii::$app->request->post();
        $selections = ArrayHelper::getValue($post_buffer, 'selection', []);
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        $tableButtonSubmit = ArrayHelper::getValue($post_buffer, 'table_button_submit', '');
        $portfolio = $portfolioLoader->loadLapResults($post_buffer['puid'], $post_buffer['luid']);
        if (!is_array($portfolio->return->LapResultStrings)) {
            $portfolio->return->LapResultStrings = [$portfolio->return->LapResultStrings];
        }
        if (isset($post_buffer['ref_UID'], $tableButtonSubmit)) {
            $result = null;
            if ($tableButtonSubmit == 'save') {
                try {
                    $portfolio_buffer = Yii::$app->portfolioTable->rewritePortfolio($portfolio, $post_buffer['new_table'], $post_buffer['ref_UID'], $post_buffer['stringIndex']);
                } catch (Throwable $th) {
                    Yii::error($th->getMessage(), 'actionTabular');

                    Yii::$app->response->statusCode = 400;
                    return 'Возникла неизвестная ошибка сохранения. Обратитесь к администратору.';
                }

                if (empty($post_buffer['stringIndex'])) {
                    $result = Yii::$app->getPortfolioService->saveLapResult([
                        'PlanUID' => $post_buffer['puid'],
                        'LapUID' => $post_buffer['luid'],
                        'LapResult' => $portfolio_buffer['return']['LapResultStrings']
                    ]);
                } else {
                    $result = Yii::$app->getPortfolioService->saveLapResult([
                        'PlanUID' => $post_buffer['puid'],
                        'LapUID' => $post_buffer['luid'],
                        'LapResult' => $portfolio_buffer['return']['LapResultStrings'][$post_buffer['stringIndex']]
                    ]);
                }
                $portfolio = $portfolioLoader->loadLapResults($post_buffer['puid'], $post_buffer['luid']);
            } elseif ($tableButtonSubmit == 'delete' && count($selections) > 0) {
                $portfolio_buffer = Yii::$app->portfolioTable->deletePortfolio($portfolio, $post_buffer['new_table'], $selections, $post_buffer['ref_UID'], $post_buffer['stringIndex']);

                if (empty($post_buffer['stringIndex'])) {
                    $result = Yii::$app->getPortfolioService->saveLapResult([
                        'PlanUID' => $post_buffer['puid'],
                        'LapUID' => $post_buffer['luid'],
                        'LapResult' => $portfolio_buffer['return']['LapResultStrings']
                    ]);
                } else {
                    $result = Yii::$app->getPortfolioService->saveLapResult([
                        'PlanUID' => $post_buffer['puid'],
                        'LapUID' => $post_buffer['luid'],
                        'LapResult' => $portfolio_buffer['return']['LapResultStrings'][$post_buffer['stringIndex']]
                    ]);
                }
                $portfolio = $portfolioLoader->loadLapResults($post_buffer['puid'], $post_buffer['luid']);
            } elseif ($tableButtonSubmit == 'delete' && count($selections) < 1) {
                Yii::$app->response->statusCode = 400;

                return 'Ошибка. Вы не выбрали не одного поля для удаления.';
            } elseif ($tableButtonSubmit == 'add') {
                try {
                    $portfolio = Yii::$app->portfolioTable->addPortfolio($portfolio, $post_buffer['ref_UID'], $post_buffer['stringIndex']);
                } catch (Throwable $th) {
                    Yii::error($th->getMessage(), 'actionTabular');

                    Yii::$app->response->statusCode = 400;
                    return 'Возникла неизвестная ошибка добавления новой записи. Обратитесь к администратору.';
                }
                $result = (object)['return' => (object)['Result' => 'Success']];
            }

            if (!$result) {
                Yii::$app->response->statusCode = 400;
                $error = Yii::$app->session->getFlash('warning');
                if ($error && is_array($error)) {
                    $error = $error[0];
                }
                return $error ?: 'Возникла неизвестная ошибка. Обратитесь к администратору.';
            }

            return Yii::$app->portfolioTable->drawTable(
                $portfolio,
                $post_buffer['puid'],
                $post_buffer['luid'],
                $post_buffer['ref_UID'],
                $post_buffer['stringIndex'],
                $result->return->Result
            );
        } elseif ($post_buffer['success'] == 'Success') {
            return Yii::$app->portfolioTable->drawTable(
                $portfolio,
                $post_buffer['puid'],
                $post_buffer['luid'],
                $post_buffer['ref_UID'],
                $post_buffer['stringIndex'],
                $post_buffer['success']
            );
        }
    }

    public function actionAuthorize()
    {
        if (Yii::$app->getUser()->getIsGuest()) {
            Url::remember(Url::current());
            return $this->redirect('/user/sign-in/ologin');
        }
        $route = Yii::$app->request->referrer;
        if (strpos($route, 'sign-in/oaccept') === false) {
            Url::remember(Url::current());
            return $this->redirect('/user/sign-in/oaccept');
        }
        
        $module = Yii::$app->getModule('oauth2');
        $response = $module->handleAuthorizeRequest(!Yii::$app->getUser()->getIsGuest(), Yii::$app->getUser()->getId());

        
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        return $response->getParameters();
    }

    public function actionDownload($id = null, $key = null)
    {
        if (is_null($id) && !is_null($key)) {
            
            $id = $key;
        }

        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $attachment = Attachment::findOne((int)$id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($attachment != null) {
            $user = Yii::$app->user->identity;
            if ($attachment->checkAccess($user)) {
                $abs_path = $attachment->getAbsPath();
                if ($abs_path && file_exists($abs_path)) {
                    return Yii::$app->response->sendFile(
                        $abs_path,
                        $this->filterFilename($attachment->filename),
                        [
                            'mimeType' => $attachment->getMimeType(),
                            'inline' => $attachment->extension === 'pdf'
                        ]
                    );
                }
                return Yii::t(
                    'abiturient/attachment-widget',
                    'Текст сообщения об отсутствии файла для виджета сканов: `Невозможно получить файл.`'
                );
            }
            return Yii::t(
                'abiturient/attachment-widget',
                'Текст сообщения об отсутствии доступа к записи для виджета сканов: `У вас нет доступа для скачивания этого файла.`'
            );
        }
        return Yii::t(
            'abiturient/attachment-widget',
            'Текст сообщения об записи об таком файле для виджета сканов: `Файл не найден.`'
        );
    }

    public function actionDownloadRegulationFile($id)
    {
        $regulation = Regulation::findOne((int)$id);
        if ($regulation === null || !$regulation->isFileContent()) {
            throw new NotFoundHttpException();
        }
        $abs_path = $regulation->getAbsPath();
        if ($abs_path && file_exists($abs_path)) {
            return Yii::$app->response->sendFile($abs_path, $regulation->content_file, ['mimeType' => $regulation->getMimeType(), 'inline' => $regulation->content_file_extension === 'pdf']);
        }

        throw new UserException("Невозможно получить файл \"{$regulation->name}\". Обратитесь к администратору.");
    }

    public function actionDownloadTemplate($name)
    {
        $file = DocumentTemplate::findOne(['name' => $name]);
        if ($file !== null) {
            $abs_path = $file->getAbsPath();
            if ($abs_path && file_exists($abs_path) && !is_dir($abs_path)) {
                return Yii::$app->response->sendFile(
                    $abs_path,
                    FilterFilename::sanitize($file->filename)
                );
            } else {
                throw new UserException("Документ: \"{$file->description}\" недоступен. Обратитесь к администратору.");
            }
        }
    }

    public function actionDeletefile($key = null, bool $redirect_back = false)
    {
        if (Yii::$app->request->isPost) {
            $key = Yii::$app->request->post('key');
        }

        $user = Yii::$app->user->identity;
        if ($this->loadScansService->deleteAttachedFile($user, $key)) {
            if ($redirect_back) {
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }
            return true;
        }
    }

    public function actionDownloadia($id)
    {
        $ind = IndividualAchievement::findOne((int)$id);
        if ($ind != null) {
            $user = Yii::$app->user->identity;
            if ($ind->checkAccess($user)) {

                $zip = new \ZipArchive();
                $collection = $ind->attachmentCollection;

                $filename = $this->filterFilename("{$collection->getAttachmentTypeName()}.zip");

                if ($zip->open(Yii::getAlias("@storage/web/tempZip/{$filename}"), \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    throw new UserException('Не удалось создать архив.');
                }
                $user = Yii::$app->user->identity;
                foreach ($collection->attachments as $key => $attachment) {
                    if ($attachment->checkAccess($user)) {
                        $abs_path = $attachment->getAbsPath();
                        if ($abs_path && file_exists($abs_path)) {
                            $number = $key + 1;
                            $zip->addFile($abs_path, $this->filterFilename("{$number}. " . $attachment->getAttachmentTypeName() . '.' . $attachment->extension));
                        }
                    }
                }
                if ($zip->numFiles > 0) {
                    $pathToZipArchive = $zip->filename;
                    $zip->close();
                    return Yii::$app->response->sendFile($pathToZipArchive, $filename)->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) {
                        unlink($event->data);
                    }, $pathToZipArchive);
                } else {
                    throw new UserException('Нет файлов для отправки.');
                }
            }
        }
        throw new NotFoundHttpException('Не удалось найти индивидуальное достижение.');
    }

    public function actionDeleteia($id)
    {
        $individualAchievement = IndividualAchievement::findOne((int)$id);
        if (
            $individualAchievement != null &&
            !$individualAchievement->read_only
        ) {
            $user = Yii::$app->user->identity;
            if ($individualAchievement->checkAccess($user)) {
                if ($individualAchievement->archive()) {
                    foreach ($individualAchievement->attachments as $attachment) {
                        $attachment->safeDelete($user);
                    }
                    $individualAchievement->application->resetStatus();
                }
            }
        }
        if (Yii::$app->request->referrer) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->goHome();
        }
    }

    public function actionDownloadagreement($id)
    {
        $attachment = AdmissionAgreement::findOne((int)$id);
        if ($attachment != null) {
            $user = Yii::$app->user->identity;
            if ($attachment->checkAccess($user)) {
                $abs_path = $attachment->getAbsPath();
                if ($abs_path && file_exists($abs_path)) {
                    Yii::$app->response->sendFile($abs_path, $this->filterFilename($attachment->filename));
                } else {
                    throw new UserException("Невозможно скачать согласие на зачисление. Файл \"{$abs_path}\" отсутствует. Необходимо повторно прикрепить файл согласия на зачисление в личном кабинете поступающего.");
                }
            } else {
                throw new UserException("Невозможно скачать согласие на зачисление. Отказано в доступе");
            }
        }
    }

    public function actionDownloadAgreementDecline($id)
    {
        $attachment = AgreementDecline::findOne((int)$id);
        if ($attachment != null) {
            $user = Yii::$app->user->identity;
            if ($attachment->checkAccess($user)) {
                $path = $attachment->getAbsPath();
                if ($path && file_exists($path)) {
                    Yii::$app->response->sendFile($path, $this->filterFilename($attachment->filename));
                } else {
                    throw new UserException("Невозможно скачать отказ согласия на зачисление. Файл \"{$path}\" отсутствует. Необходимо повторно прикрепить файл отказа согласия на зачисление в личном кабинете поступающего.");
                }
            } else {
                throw new UserException("Невозможно скачать отказ согласия на зачисление. Отказано в доступе");
            }
        }
    }

    public function actionDownloadForm($id, $type)
    {
        $file = null;
        $user = Yii::$app->user->identity;
        switch ((int)$type) {
            case (PrintForm::TYPE_PERSONAL_RECEIPT):
                $application = BachelorApplication::findOne((int)$id);
                $printform = new PrintForm();
                $printform->model = $application;
                $printform->type = PrintForm::TYPE_PERSONAL_RECEIPT;
                if ($printform != null && $printform->CheckFileExist() && $printform->checkAccess($user->id)) {
                    $file = $printform->getFullPath();
                }
                break;
            default:
                return null;
        }
        if ($file != null) {
            Yii::$app->response->sendFile($file);
        }
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        $isTechnicalWorks = false;

        if ($exception == null) {
            return;
        }

        $message = Yii::t('yii', 'An internal server error occurred.');
        $name = Yii::t('yii', 'Error');

        if ($exception instanceof ErrorException && strpos($exception->getMessage(), 'SOAP-ERROR') !== false) {
            return;
        }

        $code = $exception instanceof HttpException ? $exception->statusCode : $exception->getCode();

        if ($exception instanceof Exception) {
            $name = $exception->getName();
        }

        if ($exception instanceof UserException || $exception instanceof soapException) {
            if (substr($code, 0, 2) == '11') {
                $name = Yii::t(
                    'server/errors',
                    'Текст сообщения ошибке доступа к сервера: `Ошибка доступа к серверу`'
                );
            }

            if ($code == '11001') {
                $name = Yii::$app->configurationManager->getText('techworks_message');
                $isTechnicalWorks = true;
            }

            if (substr($code, 0, 2) == '33') {
                $name = Yii::t(
                    'server/errors',
                    'Текст сообщения ошибке доступа к сервера: `Ошибка доступа к серверу`'
                );
            }

            if ($code == '33001') {
                $name = Yii::t(
                    'server/errors',
                    'Текст сообщения ошибки сервера, когда ЛК переведён в "технический" режим: `Извините, в данный момент проводятся технические работы.`'
                );
                $isTechnicalWorks = true;
            }
        }

        if ($code && !$isTechnicalWorks) {
            $name .= " (#$code)";
        }

        if ($exception instanceof UserException || $exception instanceof soapException) {
            $message = $exception->getMessage();
        }

        if ($code == '22002') {
            $name = Yii::t(
                'server/errors',
                'Текст сообщения ошибки сервера, при загрузке анкеты из Информационной системы вуза: `Ошибка получения анкеты`'
            );
            $message = Yii::t(
                'server/errors',
                'Текст сообщения ошибки сервера, при загрузке заявления из 1С: `В информационной базе не хватает данных для Вашей идентификации. Просим обратиться в приемную комиссию`'
            );
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message";
        }

        $version1C = Yii::$app->releaseVersionProvider->getVersion();

        $os_info = php_uname('s') . ' ' . php_uname('v') . ' ' . php_uname('m');

        return $this->render(
            'error',
            [
                'name' => $name,
                'message' => $message,
                'isTechnicalWorks' => $isTechnicalWorks,
                'exception' => $exception,
                'versionPortal' => Yii::$app->version,
                'version1C' => $version1C,
                'os_info' => $os_info,
                'versionPHP' => phpversion(),
            ]
        );
    }

    





    private function setFlashOnSave(bool $saveSuccess, bool $hasChangedAttributes): void
    {
        if (!$hasChangedAttributes) {
            $alertBody = Yii::$app->configurationManager->getText('no_data_saved_text');
            $alertClass = 'alert-warning';
        }
        if ($saveSuccess && $hasChangedAttributes) {
            $alertBody = Yii::t(
                'abiturient/bachelor/accounting-benefits/all',
                'Текст сообщения успешного сохранения формы на стр. особых условий поступления: `Форма сохранена успешно.`'
            );
            $alertClass = 'alert-success';
        }

        Yii::$app->session->setFlash('alert', [
            'body' => $alertBody,
            'options' => ['class' => $alertClass]
        ]);
    }

    




    public function actionAccountingBenefits($id = null)
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->benefitsService->saveNewBenefits(Yii::$app->user->identity, $id);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    




    public function actionTargetReception($id = null)
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->targetReceptionsService->saveNewTargets(Yii::$app->user->identity, $id);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    




    public function actionAccountingOlympiads($id = null)
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->olympiadsService->saveNewOlympiads(Yii::$app->user->identity, $id);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    


    public function actionEditBenefits()
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->benefitsService->editBenefits(Yii::$app->user->identity);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    


    public function actionEditOlympiads()
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->olympiadsService->editOlympiads(Yii::$app->user->identity);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    


    public function actionEditTarget()
    {
        [
            $_,
            $saveSuccess,
            $hasChangedAttributes
        ] = $this->targetReceptionsService->editTarget(Yii::$app->user->identity);

        $this->setFlashOnSave($saveSuccess, $hasChangedAttributes);

        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    




    public function actionDeleteBenefits($id = null)
    {
        $this->benefitsService->archiveBenefits($id, Yii::$app->user->identity);
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    




    public function actionDeleteTarget($id = null)
    {
        $this->targetReceptionsService->archiveTargerReceprion($id, Yii::$app->user->identity);
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    





    public function actionOlympType($app_id, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->olympiadsService->getOlympTypeDataForSelect($app_id, $id = null);
    }

    




    public function actionDocType($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->benefitsService->getDocTypeDataForSelect($id);
    }

    


    public function actionDocTypeOlympiads($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->olympiadsService->getDocTypeOlympiadsDataForSelect($id);
    }

    




    public function actionDownloadBenefits($id = null)
    {
        [
            'filename' => $filename,
            'pathToZipArchive' => $pathToZipArchive,
        ] = $this->benefitsService->downloadBenefits(Yii::$app->user->identity, $id);

        return Yii::$app->response->sendFile($pathToZipArchive, $filename)
            ->on(
                Response::EVENT_AFTER_SEND,
                function ($event) {
                    unlink($event->data);
                },
                $pathToZipArchive
            );
    }

    




    public function actionDownloadTarget($id = null)
    {
        [
            'filename' => $filename,
            'pathToZipArchive' => $pathToZipArchive,
        ] = $this->targetReceptionsService->downloadTargets(Yii::$app->user->identity, $id);

        return Yii::$app->response->sendFile($pathToZipArchive, $filename)
            ->on(
                Response::EVENT_AFTER_SEND,
                function ($event) {
                    unlink($event->data);
                },
                $pathToZipArchive
            );
    }

    public function actionSelectedTabularElement($query = '', $filters = '', $ref_class = 'Справочник.ФизическиеЛица')
    {
        if ($filters) {
            $filters = json_decode(base64_decode($filters));
        } else {
            $filters = ['Operator' => 'And'];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($query)) {
            return null;
        }

        $response = Yii::$app->soapClientStudent->load(
            'GetReferences',
            [
                'Text' => $query,
                'Filters' => ['Operator' => 'And'],
                'TextFilterType' => 'BeginningOfLine',
                'ReferenceClassName' => $ref_class,
            ]
        );

        if ($response === false) {
            return null;
        }
        if (isset($response->return, $response->return->References)) {
            if (!is_array($response->return->References)) {
                $response->return->References = [$response->return->References];
            }
            $out = [];
            foreach ($response->return->References as $ref) {
                $id = base64_encode(json_encode([
                    'ReferenceName' => $ref->ReferenceName,
                    'ReferenceId' => $ref->ReferenceId,
                    'ReferenceUID' => $ref->ReferenceUID,
                    'ReferenceClassName' => $ref->ReferenceClassName,
                ]));
                $out[] = [
                    'id' => $id,
                    'text' => $ref->ReferenceName,
                ];
            }

            return ['results' => $out];
        }
        return null;
    }

    public function actionDownloadPaidContract($spec_id)
    {
        $speciality = BachelorSpeciality::findOne($spec_id);
        if (empty($speciality) || EmptyCheck::isEmpty($speciality->paid_contract_guid)) {
            throw new UserException('Не удалось скачать договор');
        }
        $data = [
            'ContractRef' => $speciality->buildAndUpdateContractRefFor1C()
        ];
        $result = Yii::$app->soapClientAbit->load('GetContractReport', $data);
        if ($result === false) {
            throw new ServerErrorHttpException('Не удалось подключиться к серверу 1С');
        }
        $response = $result->return->UniversalResponse;
        if (isset($response->Complete) && $response->Complete == 1) {
            $file = $result->return->ContractFile;
            $ext = mb_strtolower($file->FileExt);
            if (EmptyCheck::isEmpty($ext)) {
                $ext = 'pdf';
            }
            Yii::$app->response->sendContentAsFile(base64_decode($file->FileBinaryCode), $this->filterFilename("{$file->FileName}.{$ext}"));
        } else {
            throw new ServerErrorHttpException($response->Description);
        }
    }

    public function actionFilterOlympiads(int $app_id)
    {
        [$kind_name, $class_uid, $year, $profile_uid] = Yii::$app->request->post('depdrop_parents');
        $application = $this->olympiadsService->getApplication($app_id);
        $olympiadsService = $this->olympiadsService;

        $campaign_ref_uid = $application->type->rawCampaign->referenceType->reference_uid;
        $cache_key = "filter_olympiads_{$campaign_ref_uid}_{$kind_name}_{$class_uid}_{$year}_{$profile_uid}";
        return Yii::$app->cache->getOrSet(
            $cache_key,
            function () use (
                $olympiadsService,
                $year,
                $profile_uid,
                $campaign_ref_uid,
                $class_uid,
                $kind_name
            ) {
                return $olympiadsService->getFilterOlympiadsForCache(
                    $year,
                    $profile_uid,
                    $campaign_ref_uid,
                    $class_uid,
                    $kind_name
                );
            },
            3600
        );
    }

    public function actionDocumentTypeRules()
    {
        if (RulesProviderByDocumentType::isDisabled()) {
            return $this->asJson(null);
        }
        $document_type_id = Yii::$app->request->post('document_type_id');
        $document_type = $document_type_id ? DocumentType::findOne((int)$document_type_id) : null;
        $result = [];
        $one_s_props = array_values(RulesProviderByDocumentType::getOneSSettingsMap());
        foreach ($one_s_props as $one_s_prop) {
            [$required, $used] = [false, true];
            if ($document_type) {
                [$required, $used] = DocumentTypePropertiesSetting::getPropertySetting($document_type, $one_s_prop);
            }
            $result[$one_s_prop] = [
                'required' => $required,
                'used' => $used,
            ];
        }
        return $this->asJson($result);
    }

    private function filterFilename(string $filename): string
    {
        return FilterFilename::sanitize($filename);
    }

    public function actionDownloadAttachmentTypeTemplate(int $id)
    {
        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $attachmentTypeTemplate = AttachmentTypeTemplate::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($attachmentTypeTemplate) {
            $path = $attachmentTypeTemplate->getAbsPath();
            if (!$path || !file_exists($path)) {
                return Yii::t(
                    'abiturient/download-attachment-type-template',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить файл.`'
                );
            }
            $mimeType = 'application/image';
            if (strpos($path, '.pdf') !== false) {
                $mimeType = 'application/pdf';
            }

            return Yii::$app->response->sendFile(
                $path,
                FilterFilename::sanitize($attachmentTypeTemplate->filename),
                ['inline' => true, 'mimeType' => $mimeType]
            );
        }

        return Yii::t(
            'abiturient/download-attachment-type-template',
            'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл не найден.`'
        );
    }

    public function actionDownloadInstructionAttachment(int $id)
    {
        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $setting = MainPageSetting::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (
            $setting &&
            $instruction = MainPageSetting::getRelatedInstruction($setting)
        ) {
            if (!$instruction instanceof MainPageInstructionFile) {
                return Yii::t(
                    'abiturient/download-instruction-attachment',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить информацию о файле.`'
                );
            }

            $path = $instruction->getAbsPath();
            if (!$path || !file_exists($path)) {
                return Yii::t(
                    'abiturient/download-instruction-attachment',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить файл.`'
                );
            }

            return Yii::$app->response->sendFile(
                $path,
                FilterFilename::sanitize($instruction->filename)
            );
        }

        return Yii::t(
            'abiturient/download-instruction-attachment',
            'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл не найден.`'
        );
    }
}
