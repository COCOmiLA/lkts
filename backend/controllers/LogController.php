<?php

namespace backend\controllers;

use backend\models\search\SystemLogInfoSearch;
use backend\models\search\SystemLogSearch;
use backend\models\SystemLog;
use backend\models\SystemLogInfo;
use common\models\DebuggingSoap;
use common\models\DummySoapResponse;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;





class LogController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'clear' => ['post'],
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

    



    public function actionIndex()
    {
        $searchModel = new SystemLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (strcasecmp(Yii::$app->request->method, 'delete') == 0) {
            SystemLog::deleteAll($dataProvider->query->where);
            return $this->refresh();
        }
        $dataProvider->sort = [
            'defaultOrder' => ['log_time' => SORT_DESC]
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInfo()
    {
        $searchModel = new SystemLogInfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (strcasecmp(Yii::$app->request->method, 'del') == 0) {
            SystemLogInfo::deleteAll($dataProvider->query->where);
            return $this->refresh();
        }
        $dataProvider->sort = [
            'defaultOrder' => ['log_time' => SORT_DESC]
        ];

        return $this->render('info', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDebugging()
    {
        $model = null;
        $newSoapModel = new DummySoapResponse();

        try {
            $model = DebuggingSoap::getInstance();
            if ($model->load(Yii::$app->request->post())) {
                $model->save();
            }
            if (Yii::$app->request->isPost && $newSoapModel->load(Yii::$app->request->post()) && $newSoapModel->validate()) {
                $newSoapModel->saveMethod();
                $newSoapModel = new DummySoapResponse();
            }
        } catch (\Throwable $e) {
            Yii::error('Не установлена таблица "debuggingsoap"');
        }
        return $this->render(
            'debugging',
            compact('model', 'newSoapModel')
        );
    }

    public function actionDeleteDummySoap($id)
    {
        DummySoapResponse::deleteAll(['id' => $id]);
        return $this->redirect('/admin/log/debugging');
    }

    




    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionV($id)
    {
        return $this->render('v', [
            'model' => $this->findModelInfo($id),
        ]);
    }

    





    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionDel($id)
    {
        $this->findModelInfo($id)->delete();

        return $this->redirect(['info']);
    }

    






    protected function findModel($id)
    {
        if (($model = SystemLog::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findModelInfo($id)
    {
        if (($model = SystemLogInfo::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
