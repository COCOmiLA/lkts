<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\controllers;

use common\modules\student\components\forumIn\forum\bizley\podium\src\filters\AccessControl;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Content;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms\LoginForm;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms\ReactivateForm;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms\ResetForm;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\PodiumCache;
use Yii;
use yii\base\Action;
use yii\helpers\Html;
use yii\web\Response;








class AccountController extends BaseController
{
    


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function ($rule, $action) {
                    return $this->module->goPodium();
                },
                'rules' => [
                    ['class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\filters\InstallRule'],
                    [
                        'allow' => true,
                        'actions' => ['new-email']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['?']
                    ],
                ],
            ],
        ];
    }

    


    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'testLimit' => 1
            ],
        ];
    }

    





    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if ($this->accessType === 0) {
            return $this->module->goPodium();
        }
        return true;
    }

    




    public function actionActivate($token)
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to activate your account.'));
            return $this->module->goPodium();
        }

        $model = User::findByActivationToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
            return $this->module->goPodium();
        }
        $model->scenario = 'token';
        if ($model->activate()) {
            PodiumCache::clearAfter('activate');
            Log::info('Account activated', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Your account has been activated. You can sign in now.'));
        } else {
            Log::error('Error while activating account', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your account. Contact administrator about this problem.'));
        }
        return $this->module->goPodium();
    }

    



    public function actionLogin()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please use application Login form to sign in.'));
            return $this->module->goPodium();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->module->goPodium();
        }

        return $this->render('login', ['model' => $model]);
    }

    




    public function actionNewEmail($token)
    {
        $model = User::findByEmailToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
            return $this->module->goPodium();
        }

        $model->scenario = 'token';
        if ($model->changeEmail()) {
            Log::info('Email address changed', $model->id, __METHOD__);
            Yii::$app->session->removeFlash('warning'); 
            $this->success(Yii::t('podium/flash', 'Your new e-mail address has been activated.'));
        } else {
            Log::error('Error while activating email', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your new e-mail address. Contact administrator about this problem.'));
        }
        return $this->module->goPodium();
    }

    




    public function actionPassword($token)
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to change your account password.'));
            return $this->module->goPodium();
        }

        $model = User::findByPasswordResetToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided password reset token is invalid or expired.'));
            return $this->module->goPodium();
        }
        $model->scenario = 'passwordChange';
        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Log::info('Password changed', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Your account password has been changed.'));
            return $this->module->goPodium();
        }
        return $this->render('password', ['model' => $model]);
    }

    



    public function actionRegister()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', "Please use application's Register form to sign up."));
            return $this->module->goPodium();
        }

        if ($this->module->podiumConfig->get('registration_off') == '1') {
            $this->info(Yii::t('podium/flash', 'User registration is currently not allowed.'));
            return $this->module->goPodium();
        }

        $model = new User();
        $model->scenario = 'register';
        if ($model->load(Yii::$app->request->post())) {
            $result = $model->register();
            if ($result == User::RESP_OK) {
                Log::info('Activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success(Yii::t('podium/flash', 'Your account has been created but it is not active yet. Click the activation link that will be sent to your e-mail address in few minutes.'));
                return $this->module->goPodium();
            }
            if ($result == User::RESP_EMAIL_SEND_ERR) {
                Log::warning('Error while queuing activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->warning(Yii::t('podium/flash', 'Your account has been created but it is not active yet. Unfortunately there was some error while sending you the activation link. Contact administrator about this or try to {resend the link}.', [
                    'resend the link' => Html::a(Yii::t('podium/flash', 'resend the link'), ['account/reactivate'])
                ]));
                return $this->module->goPodium();
            }
            if ($result == User::RESP_NO_EMAIL_ERR) {
                Log::error('Error while queuing activation link - no email set', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about activating.'));
                return $this->module->goPodium();
            }
        }
        $model->captcha = null;

        return $this->render('register', [
            'model' => $model,
            'terms' => Content::fill(Content::TERMS_AND_CONDS)]
        );
    }

    



    public function actionReset()
    {
        return $this->reformRun(
            Yii::t('podium/flash', 'Please contact the administrator to reset your account password.'),
            new ResetForm(),
            [
                'error' => 'Error while queuing password reset link',
                'info' => 'Password reset link queued',
                'method' => __METHOD__
            ],
            'reset'
        );
    }

    



    public function actionReactivate()
    {
        return $this->reformRun(
            Yii::t('podium/flash', 'Please contact the administrator to reactivate your account.'),
            new ReactivateForm(),
            [
                'error' => 'Error while queuing reactivation link',
                'info' => 'Reactivation link queued',
                'method' => __METHOD__
            ],
            'reactivate'
        );
    }

    







    protected function reformRun($componentInfo, $model, $log, $view)
    {
        if ($this->module->userComponent !== true) {
            $this->info($componentInfo);
            return $this->module->goPodium();
        }

        if ($model->load(Yii::$app->request->post())) {
            list($error, $message, $back) = $model->run();
            if ($error) {
                Log::error($log['error'], !empty($model->user->id) ? $model->user->id : null, $log['method']);
                if (!empty($message)) {
                    $this->error($message);
                }
            } else {
                Log::info($log['info'], $model->user->id, $log['method']);
                if (!empty($message)) {
                    $this->success($message);
                }
            }
            if ($back) {
                return $this->module->goPodium();
            }
        }
        return $this->render($view, ['model' => $model]);
    }
}
