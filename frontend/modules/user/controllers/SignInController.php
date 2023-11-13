<?php

namespace frontend\modules\user\controllers;

use common\commands\command\AddToTimelineCommand;
use common\commands\command\SendEmailCommand;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\secureUrlManager\SecureUrlManager;
use common\components\UserEmailConfirmTokenManager;
use common\models\dictionary\Contractor;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\repositories\UserRegistrationConfirmTokenRepository;
use common\models\Rolerule;
use common\models\User;
use common\modules\abiturient\models\PassportData;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenExpiredException;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenNotFoundException;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenValidationException;
use frontend\modules\user\models\AbiturientRecoverForm;
use frontend\modules\user\models\AbiturientSignupForm;
use frontend\modules\user\models\AccessForm;
use frontend\modules\user\models\ChangePassword;
use frontend\modules\user\models\EmailCodeConfirmForm;
use frontend\modules\user\models\LoginForm;
use frontend\modules\user\models\PasswordResetRequestForm;
use frontend\modules\user\models\ResetPasswordForm;
use Throwable;
use Yii;
use yii\authclient\AuthAction;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\Response;
use kartik\form\ActiveForm;

class SignInController extends Controller
{
    public function actions()
    {
        return [
            'oauth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'successOAuthCallback']
            ]
        ];
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'abiturient-access',
                            'abiturient-recover',
                            'abiturient-signup',
                            'confirm-email-by-link',
                            'login',
                            'logout',
                            'oauth',
                            'ologin',
                            'request-password-reset',
                            'reset-password',
                            'signup',
                        ],
                        'allow' => true,
                        'roles' => ['?']
                    ],
                    [
                        'actions' => [
                            'abiturient-access',
                            'abiturient-recover',
                            'login',
                            'oauth',
                            'request-password-reset',
                            'reset-password',
                            'signup',
                        ],
                        'allow' => false,
                        'roles' => ['@'],
                        'denyCallback' => function () {
                            return Yii::$app->controller->redirect(['/user/default/index']);
                        }
                    ],
                    [
                        'actions' => [
                            'logout',
                            'oaccept'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['userset'],
                        'allow' => false,
                        'roles' => [
                            'student',
                            'teacher'
                        ],
                    ],
                    [
                        'actions' => ['userset'],
                        'allow' => true,
                        'roles' => ['user'],
                    ],
                    [
                        'actions' => [
                            'user-access',
                            'change-password'
                        ],
                        'allow' => true,
                        'roles' => [
                            'student',
                            'teacher',
                            'user'
                        ]
                    ],
                    [
                        'actions' => ['user-access'],
                        'allow' => false,
                        'roles' => [
                            'abiturient',
                            '?'
                        ]
                    ],
                    
                    [
                        'actions' => [
                            'confirm-email',
                            'confirm-email-by-link',
                            'repeat-email-confirm'
                        ],
                        'allow' => true,
                        'roles' => ['abiturient'],
                    ],
                ],
                'denyCallback' => function () {
                    $this->redirect('/');
                }
            ]
        ];
    }

    public function actionLogin($access = null, $error = null)
    {
        $isAbit = false;
        $model = new LoginForm();
        Yii::$app->response->cookies->remove('emailToRecover');

        if (Yii::$app->request->isAjax) {
            $model->load($_POST);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate(['reCaptcha']) &&
            $model->login()
        ) {
            if ($model->needToChooseRole) {
                
                $user = Yii::$app->user->identity;
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'user',
                    'event' => 'signin',
                    'data' => [
                        'public_identity' => $user->getPublicIdentity(),
                        'user_id' => $user->getId(),
                    ]
                ]));
                return $this->redirect(Url::toRoute('sign-in/userset'));
            } else {
                $roles = \Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
                if (isset($roles['administrator'])) {
                    $user = Yii::$app->user->identity;
                    Yii::$app->commandBus->handle(new AddToTimelineCommand([
                        'category' => 'user',
                        'event' => 'signin',
                        'data' => [
                            'public_identity' => $user->getPublicIdentity(),
                            'user_id' => $user->getId(),
                        ]
                    ]));
                    return $this->redirect('/site/index');
                }

                if (!empty(Yii::$app->db->getTableSchema('rolerule'))) {
                    $_rolerule = Rolerule::find()->limit(1)->one();
                    if (isset($roles['manager']) && $_rolerule->abiturient == 1) {
                        $user = Yii::$app->user->identity;
                        Yii::$app->commandBus->handle(new AddToTimelineCommand([
                            'category' => 'user',
                            'event' => 'signin',
                            'data' => [
                                'public_identity' => $user->getPublicIdentity(),
                                'user_id' => $user->getId(),
                            ]
                        ]));
                        return $this->redirect('/site/index');
                    }

                    if ($_rolerule->abiturient == 1) {
                        $user = Yii::$app->user->identity;
                        Yii::$app->commandBus->handle(new AddToTimelineCommand([
                            'category' => 'user',
                            'event' => 'signin',
                            'data' => [
                                'public_identity' => $user->getPublicIdentity(),
                                'user_id' => $user->getId(),
                            ]
                        ]));
                        return $this->redirect('/site/index');
                    } else {
                        $error = 'emptyRolesAbiturienta';
                        Yii::$app->user->logout();
                    }
                } else {
                    $error = 'emptyRoleRule';
                    Yii::$app->user->logout();
                }
            }
        } else {
            if (!empty(Yii::$app->db->getTableSchema('rolerule'))) {
                $_rolerule = Rolerule::find()->limit(1)->one();
                if ($_rolerule->abiturient == 1) {
                    $isAbit = true;
                }
            } else {
                $error = 'emptyRoleRule';
            }
        }
        return $this->render('login', [
            'model' => $model,
            'error' => $error,
            'access' => $access,
            'isAbit' => $isAbit,
        ]);
    }

    public function actionOlogin()
    {
        $model = new LoginForm();
        if (Yii::$app->request->isAjax) {
            $model->load($_POST);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate(['reCaptcha']) &&
            $model->login()
        ) {
            return $this->redirect(Url::to('/user/sign-in/oaccept'));
        } else {
            return $this->render('ologin', ['model' => $model]);
        }
    }

    public function actionOaccept()
    {
        if (!Yii::$app->request->post()) {
            return $this->render('oaccept', []);
        } else {
            $accept = Yii::$app->request->post('accept');
            if ($accept == "1") {
                $this->redirect(Url::to(Url::previous()));
            } else {
                Yii::$app->response->setStatusCode(401);
                Yii::$app->response->send();
            }
        }
    }

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return $this->redirect("/user/sign-in/login");
        }

        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'logout',
            'data' => [
                'public_identity' => $user->getPublicIdentity(),
                'user_id' => $user->getId(),
            ]
        ]));
        Yii::$app->session->remove('transfer');
        Yii::$app->user->logout();

        return $this->redirect("/user/sign-in/login");
    }

    public function actionAbiturientSignup()
    {
        $model = new AbiturientSignupForm();
        $model->passportData = new PassportData();
        $model->passportData->setScenario(PassportData::SCENARIO_SIGN_UP);
        $model->contragent = new Contractor();
        
        $errorFrom1C = false;
        $errorPassport_type = false;
        $uid = Yii::$app->configurationManager->getCode('identity_docs_guid');
        $docs = [];
        $parent = DocumentType::findByUID($uid);
        if ($parent) {
            $docs = DocumentType::find()
                ->notMarkedToDelete()
                ->active()
                ->andWhere(['parent_key' => $parent->ref_key])
                ->andWhere(["is_folder" => false])
                ->orderBy(['ref_key' => SORT_DESC])
                ->all();
        }
        if (
            Yii::$app->request->isPost &&
            $model->load(Yii::$app->request->post()) &&
            $model->validate(['reCaptcha']) &&
            $model->passportData->load(Yii::$app->request->post())
        ) {
            $transaction = Yii::$app->db->beginTransaction();
            $model->contragent->load(Yii::$app->request->post());
            
            try {
                if ($model->passportData->notFoundContractor) {
                    $model->passportData->contractor_id = ContractorManager::Upsert(
                        $model->contragent->getAttributes(null, ['contractor_ref_id', 'status', 'archive']),
                        $model->passportData->documentType
                    )->id;
                }
                $user = $model->signup();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            $transaction->commit();
            $model->getErrors();
            if ($user && Yii::$app->getUser()->login($user)) {
                return $this->redirect("/abiturient/index");
            }
        }

        if ($model->hasErrors('email')) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'emailToRecover',
                'value' => $model->email,
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ]));
        }


        $confirmEmail = Yii::$app->configurationManager->getCode('confirm-email');
        return $this->render('abiturientSignup', [
            'model' => $model,
            'passportTypes' => ArrayHelper::map($docs, 'id', 'description'),
            'errorFrom1C' => $errorFrom1C,
            'errorPassport_type' => $errorPassport_type,
            'confirmEmail' => $confirmEmail === '1' ? true : false,
        ]);
    }

    public function actionAbiturientAccess()
    {
        $model = new AccessForm();
        $accountRecoverModel = null;
        Yii::$app->session->remove('abitRefIdToRecover');

        if (Yii::$app->request->isAjax) {
            $model->load($_POST);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate(['reCaptcha'])
        ) {
            $found_local_user = $model->getAccess();
            if ($found_local_user && !empty($model->possibleEmail)) {
                $accountRecoverModel = new PasswordResetRequestForm();
                $accountRecoverModel->email = $model->possibleEmail;
            } elseif ($model->user_ref_id != null) {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Ваши данные успешно найдены в системе вуза. Необходимо завершить регистрацию.',
                    'options' => ['class' => 'alert-success']
                ]);
                return $this->redirect(Url::toRoute(['/user/sign-in/abiturient-recover']));
            } else {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Не удалось найти профиль поступающего по предоставленным данным',
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }
        if (is_null($model->documentTypeId)) {
            $model->documentTypeId = CodeSettingsManager::GetEntityByCode('russian_passport_guid')->id;
        }
        return $this->render('abiturientAccess', [
            'model' => $model,
            'recoverModel' => $accountRecoverModel,
        ]);
    }

    




    public function actionAbiturientRecover()
    {
        $model = new AbiturientRecoverForm();
        $creatingErrors = [];

        $user_ref_id = Yii::$app->session->get('abitRefIdToRecover');
        $user_ref = StoredUserReferenceType::findOne($user_ref_id);
        if ($user_ref == null) {
            return $this->redirect(Url::toRoute(['/user/sign-in/abiturient-access']), 302);
        }
        $model->user_ref = $user_ref;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $user = User::find()
                    ->where(['or', ['user_ref_id' => $model->user_ref->id], ['guid' => $model->user_ref->reference_id]])
                    ->one();

                if ($user == null) {
                    $user = new User();
                }
                $user->guid = $model->user_ref->reference_id;
                $user->user_ref_id = $model->user_ref->id;
                $user->is_archive = false;

                if (!Yii::$app->configurationManager->signupEmailEnabled) {
                    $result = $user->createFrom1C($model->password, $user->id == null, $model->email, $creatingErrors);
                } else {
                    $result = $user->createFrom1C(null, $user->id == null, $model->email, $creatingErrors);
                }
                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                $result = false;
            }
            if ($result) {
                Yii::$app->session->remove('abitRefIdToRecover');
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => Yii::t('frontend', 'Проверьте ваш e-mail.'),
                    'options' => ['class' => 'alert-success']
                ]);

                return $this->redirect('/');
            } else {
                Yii::$app->getSession()->setFlash('userFetchingFrom1CError', $creatingErrors);
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => "Возникли ошибки при проведении операции, обратитесь к администратору приемной кампании.",
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }
        if ($model->hasErrors('email')) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'emailToRecover',
                'value' => $model->email,
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ]));
        }

        return $this->render('abiturientRecover', ['model' => $model]);
    }

    public function actionUserAccess()
    {
        $user = \Yii::$app->user->identity;
        $status = false;
        if ($user->guid != null) {
            $status = $user->setAsAbiturient();
        }
        if ($status) {
            $this->redirect(Url::toRoute(['/abiturient/index']));
        }

        return $this->render('userAccessError', [
            'user' => $user
        ]);
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => Yii::t(
                        'sign-in/request-password-reset/form',
                        'Алерт информирующий об успешной отправке письма на восстановления пароля: `Проверьте ваш e-mail.`'
                    ),
                    'options' => ['class' => 'alert-success']
                ]);

                return $this->redirect('/');
            } else {
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => Yii::t(
                        'sign-in/request-password-reset/form',
                        'Алерт информирующий о не удачной отправке письма на восстановления пароля: `Извините, мы не можем сбросить пароль для этого e-mail.`'
                    ),
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }
        $cookies = Yii::$app->request->cookies;
        if ($cookies->has('emailToRecover') && !Yii::$app->request->isPost) {
            $model->email = $cookies->getValue('emailToRecover');
        }
        return $this->render(
            'requestPasswordResetToken',
            ['model' => $model]
        );
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('alert', [
                'body' => Yii::t('frontend', 'Новый пароль был сохранен'),
                'options' => ['class' => 'alert-success']
            ]);
            return $this->redirect('/');
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    




    public function successOAuthCallback($client)
    {
        
        $attributes = $client->getUserAttributes();
        $user = User::findActive()
            ->andWhere([
                'oauth_client' => $client->getName(),
                'oauth_client_user_id' => ArrayHelper::getValue($attributes, 'id')
            ])
            ->limit(1)
            ->one();
        if (!$user) {
            $user = new User();
            $user->scenario = 'oauth_create';
            $user->username = ArrayHelper::getValue($attributes, 'login');
            $user->email = ArrayHelper::getValue($attributes, 'email');
            $user->oauth_client = $client->getName();
            $user->oauth_client_user_id = ArrayHelper::getValue($attributes, 'id');
            $password = Yii::$app->security->generateRandomString(8);
            $user->setPassword($password);
            if ($user->save()) {
                $user->afterSignup();
                $sentSuccess = Yii::$app->commandBus->handle(new SendEmailCommand([
                    'view' => 'oauth_welcome',
                    'params' => ['user' => $user, 'password' => $password],
                    'subject' => Yii::t('frontend', '{app-name} | Информация о пользователе', ['app-name' => Yii::$app->name]),
                    'to' => $user->email
                ]));
                if ($sentSuccess) {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options' => ['class' => 'alert-success'],
                            'body' => Yii::t('frontend', 'Добро пожаловать в {app-name}. E-mail с информацией о пользователе был отправлен на вашу почту.', [
                                'app-name' => Yii::$app->name
                            ])
                        ]
                    );
                }
            } else {
                
                if (User::findActive()->andWhere(['email' => $user->email])->count()) {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options' => ['class' => 'alert-danger'],
                            'body' => Yii::t('frontend', 'Пользователь с email {email} уже зарегистрирован.', [
                                'email' => $user->email
                            ])
                        ]
                    );
                } else {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options' => ['class' => 'alert-danger'],
                            'body' => Yii::t('frontend', 'Ошибка в процессе OAuth авторизации.')
                        ]
                    );
                }
            };
        }
        if (Yii::$app->user->login($user, 3600 * 24 * 30)) {
            return true;
        } else {
            throw new UserException('OAuth error');
        }
    }

    public function actionUserset($role = null)
    {
        $user = Yii::$app->user->identity;
        $student = Yii::$app->getModule('student');
        if ($user != null && $student != null && $user->guid != null) {
            $roles = $student->authManager->getRoles($user->guid);
            
            if (!isset($roles)) {
                Yii::$app->user->logout();
                return $this->redirect([
                    '/user/sign-in/login',
                    'error' => 'emptyRoles'
                ]);
            }
            if (!in_array('Teacher', $roles) && Yii::$app->session->hasFlash('ErrorRecordbooks')) {
                Yii::$app->user->logout();
                return $this->redirect([
                    '/user/sign-in/login',
                    'error' => 'emptyRecordbooks'
                ]);
            }
            if (in_array('Student', $roles) && Yii::$app->session->hasFlash('ErrorRecordbooks')) {
                Yii::$app->session->setFlash(
                    'emptyStudentRecordbooks',
                    '<b>Внимание. </b>Вы не можете войти как студент: нет данных об обучении.'
                );
            }
            
            if ($role != null && in_array($role, $roles)) {
                $auth = Yii::$app->authManager;
                $auth->assign($auth->getRole(User::mapRole($role)), $user->getId());

                return $this->redirect('/site/index');
            }
            if (!empty(Yii::$app->db->getTableSchema('rolerule'))) {
                $_rolerule = Rolerule::find()->limit(1)->one();
                $rolerule = [];
                if ($_rolerule->student == 0) {
                    $rolerule[] = 'Student';
                };
                if ($_rolerule->teacher == 0) {
                    $rolerule[] = 'Teacher';
                };
                if ($_rolerule->abiturient == 0) {
                    $rolerule[] = 'Abiturient';
                };
                $roles = array_diff($roles, $rolerule);
            }
            return $this->render('userset', ['roles' => $roles]);
        }
    }

    public function actionChangePassword()
    {
        $model = new ChangePassword();

        $reason = '';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!Yii::$app->security->validatePassword($model->password, Yii::$app->user->identity->password_hash)) {
                $reason = 'Не верно указан пароль пользователя.';
            }

            if (!$reason && $model->password == $model->new_password) {
                $reason = 'Новый пароль совпадает со старым.';
            }

            if (!$reason && $model->new_password != $model->repeat_new_password) {
                $reason = 'Новый пароль не совпадает с его подтверждением.';
            }

            if (!$reason) {
                $response = Yii::$app->soapClientStudent->load(
                    'ChangeLoginPassword',
                    [
                        'UserId' => '',
                        'Login' => Yii::$app->user->identity->username,
                        'PasswordHash' => sha1($model->password),
                        'NewLogin' => '',
                        'NewPasswordHash' => sha1($model->new_password),
                    ]
                );

                if ($response === false) {
                    $reason = \Yii::$app->session->getFlash('ErrorSoapResponse');
                } else {
                    Yii::$app->user->identity->setPassword($model->new_password);
                    Yii::$app->user->identity->save();

                    Yii::$app->getSession()->setFlash('alert', [
                        'body' => Yii::t('frontend', 'Новый пароль был сохранен'),
                        'options' => ['class' => 'alert-success']
                    ]);
                    return $this->redirect('/');
                }
            }
        }

        return $this->render(
            'changePassword',
            [
                'model' => $model,
                'reason' => $reason,
            ]
        );
    }

    public function actionConfirmEmail()
    {
        $model = new EmailCodeConfirmForm(Yii::$app->user->identity);

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                try {
                    if ($model->handleCode(time())) {
                        return $this->redirect('/', 301);
                    }
                } catch (EmailTokenExpiredException $e) {
                    UserEmailConfirmTokenManager::archiveAllTokensByUser($model->user);
                    Yii::$app->session->setFlash('alert-email-confirm', [
                        'body' => 'Время отведенное на подтверждение кода (' . Yii::$app->configurationManager->getSignupEmailTokenTTL() . ' минут) истекло. Пожалуйста, запросите код для подтверждения еще раз и повторите попытку.',
                        'options' => ['class' => 'alert-danger']
                    ]);
                } catch (EmailTokenNotFoundException $e) {
                    UserEmailConfirmTokenManager::archiveAllTokensByUser($model->user);
                    Yii::$app->session->setFlash('alert-email-confirm', [
                        'body' => 'В системе не найдено информации о актуальном коде для подтверждения email вашего аккаунта. Пожалуйста, запросите код для подтверждения еще раз и повторите попытку.',
                        'options' => ['class' => 'alert-danger']
                    ]);
                } catch (EmailTokenValidationException $e) {
                    UserEmailConfirmTokenManager::archiveAllTokensByUser($model->user);
                    Yii::$app->session->setFlash('alert-email-confirm', [
                        'body' => 'Вы ввели не правильный код для подтверждения. Пожалуйста, запросите код для подтверждения еще раз и повторите попытку.',
                        'options' => ['class' => 'alert-danger']
                    ]);
                }
            }
        }

        return $this->render(
            'emailConfirm',
            [
                'model' => $model
            ]
        );
    }

    public function actionConfirmEmailByLink($hash, $user_id)
    {
        $user = User::findOne($user_id);
        $token = UserRegistrationConfirmTokenRepository::findActiveTokenByHashAndUser($hash, $user);

        if ($token === null) {
            Yii::$app->session->setFlash('alert-email-confirm', [
                'body' => 'В системе не найдено информации о актуальном коде для подтверждения email вашего аккаунта. Пожалуйста, запросите код для подтверждения еще раз и повторите попытку.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect('/user/sign-in/confirm-email', 301);
        }

        if ($token->isExpired(time())) {
            Yii::$app->session->setFlash('alert-email-confirm', [
                'body' => 'Время отведенное на подтверждение email (' . Yii::$app->configurationManager->getSignupEmailTokenTTL() . ' минут) истекло. Пожалуйста, запросите код для подтверждения еще раз и повторите попытку.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect('/user/sign-in/confirm-email', 301);
        }

        $transaction = Yii::$app->db->beginTransaction();

        if ($transaction === null) {
            throw new UserException('Невозможно создать транзакцию');
        }
        try {
            UserEmailConfirmTokenManager::archiveAllTokensByUser($user);
            $user->addUserRegistrationConfirm();

            $transaction->commit();

            Yii::$app->session->setFlash('alert', [
                'body' => 'Ваш email успешно подтвержден.',
                'options' => ['class' => 'alert-success']
            ]);
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect('/', 301);
    }

    public function actionRepeatEmailConfirm()
    {
        $user = Yii::$app->user->identity;
        $token = UserEmailConfirmTokenManager::createUserEmailConfirmToken($user);
        if (Yii::$app->notifier->notifyAboutEmailConfirmation($user, $token)) {
            Yii::$app->session->setFlash('alert-email-confirm', [
                'body' => 'Новый код для подтверждения email успешно отправлен.',
                'options' => ['class' => 'alert-success']
            ]);
        } else {
            Yii::$app->session->setFlash('alert-email-confirm', [
                'body' => 'Ошибка почтового сервиса, обратитесь к администратору.',
                'options' => ['class' => 'alert-danger']
            ]);
        }
        return $this->redirect('/user/sign-in/confirm-email', 301);
    }
}
