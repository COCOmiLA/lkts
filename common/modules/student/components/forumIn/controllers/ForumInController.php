<?php







namespace common\modules\student\components\forumIn\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class ForumInController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['student', 'teacher']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex()
    {
        if (!Yii::$app->getModule('student')->forumInLoader->forumIsInstalled()) {
            throw new \yii\web\ServerErrorHttpException('Форум не установлен, обратитесь к администратору');
        }

        \Yii::$app->getUrlManager();

        return $this->redirect(Url::toRoute('podium/forum'));
    }
}