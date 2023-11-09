<?php








namespace backend\controllers;

use backend\models\AccountForm;
use backend\models\LoginForm;
use common\commands\command\AddToTimelineCommand;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

class SignInController extends Controller
{

    public $defaultAction = 'login';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ];
    }

    public function actions()
    {
        return [
            'avatar-upload' => [
                'class' => UploadAction::class,
                'deleteRoute' => 'avatar-delete',
                'on afterSave' => function ($event) {
                    
                    $file = $event->file;
                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
                    $file->put($img->encode());
                }
            ],
            'avatar-delete' => [
                'class' => DeleteAction::class
            ]
        ];
    }


    public function actionLogin()
    {
        $this->layout = 'base';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'category' => 'user',
                'event' => 'signin',
                'data' => [
                    'public_identity' => $user->getPublicIdentity(),
                    'user_id' => $user->getId(),
                ]
            ]));
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model
            ]);
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
        return $this->redirect('/site/index');
    }

    public function actionProfile()
    {
        $model = Yii::$app->user->identity->userProfile;
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => Yii::t('backend', 'Ваш профиль был успешно сохранен', [], $model->locale)
            ]);
            return $this->refresh();
        }
        return $this->render('profile', ['model' => $model]);
    }

    public function actionAccount()
    {
        $user = Yii::$app->user->identity;
        $model = new AccountForm();
        $model->username = $user->username;
        $model->email = $user->email;
        if ($model->load($_POST) && $model->validate()) {
            $user->username = $model->username;
            $user->email = $model->email;
            if ($model->password) {
                $user->setPassword($model->password);
            }
            $user->save();
            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => Yii::t('backend', 'Ваш аккаунт был успешно сохранен')
            ]);
            return $this->refresh();
        }
        return $this->render('account', ['model' => $model]);
    }
}
