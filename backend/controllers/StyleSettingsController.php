<?php

namespace backend\controllers;

use backend\models\FaviconSettings;
use common\models\settings\LogoSetting;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;



class StyleSettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-icon',
                    'delete-logo',
                    'index',
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    


    public function actionDeleteIcon()
    {
        $faviconModel = new FaviconSettings();

        $faviconModel->deleteFile();

        return $this->redirect('index');
    }

    




    public function actionDeleteLogo(int $id)
    {
        $logoSetting = LogoSetting::findOne(['id' => $id]);

        if ($logoSetting) {
            $logoSetting->deleteFile();
        }

        return $this->redirect('index');
    }

    


    public function actionIndex()
    {
        $faviconModel = new FaviconSettings();

        if (
            Yii::$app->request->isPost &&
            LogoSetting::loadFromPost(Yii::$app->request->post()) &&
            $faviconModel->loadFromPost(Yii::$app->request->post())
        ) {
            Yii::$app->session->setFlash(
                'alert',
                [
                    'body' => Yii::t('backend', 'Настройки сохранены'),
                    'options' => ['class' => 'alert-success']
                ]
            );
        }

        $logoModels = LogoSetting::find()->all();

        return $this->render(
            'index',
            compact([
                'logoModels',
                'faviconModel',
            ])
        );
    }
}
