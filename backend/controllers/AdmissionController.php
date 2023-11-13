<?php

namespace backend\controllers;

use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\SearchApplicationType;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class AdmissionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['post']]
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
            'time' => [
                'class' => 'common\components\EnvironmentManager\filters\TimeSyncCheckFilter',
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new SearchApplicationType();
        $params = Yii::$app->request->queryParams;
        $applicationTypesDataProvider = $searchModel->search($params);
        return $this->render('index', [
            'applicationTypesDataProvider' => $applicationTypesDataProvider,
            'searchModel' => $searchModel
        ]);
    }

    public function actionCreate()
    {
        $model = new ApplicationType();
        $redirect_to_index = false;
        $dualReceptionCompany = false;
        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();

            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    $transaction->rollBack();

                    if (isset($model->errors['campaign_id'])) { 
                        $type = ApplicationType::findOne(['campaign_id' => $model->campaign_id, 'archive' => true]);
                        if (isset($type)) { 
                            foreach (array_keys($type->getTableSchema()->columns) as $key) {
                                if (isset($model[$key])) {
                                    $type[$key] = $model[$key];
                                }
                            }
                            $type->archive = false; 
                            $redirect_to_index = true;

                            if (!$type->save()) {
                                throw new RecordNotValid($type);
                            }
                        } else {
                            $dualReceptionCompany = true;
                        }
                    }

                    $model->setIsNewRecord(true);
                } else {
                    $redirect_to_index = true;

                    $transaction->commit();
                }
                if ($redirect_to_index) {
                    return $this->redirect(['index']);
                }
            }
        }
        $campaigns = ArrayHelper::map(AdmissionCampaign::find()->where(['reception_allowed' => 1])->active()->all(), 'id', 'name');
        return $this->render('create', [
            'model' => $model,
            'campaigns' => $campaigns,
            'dualReceptionCompany' => $dualReceptionCompany
        ]);
    }

    public function actionUpdate($id)
    {
        $model = ApplicationType::findOne(['id' => $id, 'archive' => false]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        $campaigns = ArrayHelper::map(AdmissionCampaign::find()->where(['reception_allowed' => 1])->all(), 'id', 'name');
        return $this->render('update', [
            'model' => $model,
            'campaigns' => $campaigns
        ]);
    }

    public function actionDelete($id)
    {
        $type = ApplicationType::findOne(['id' => $id, 'archive' => false]);
        if (isset($type)) {
            $type->archive = true;
            if (!$type->save(true, ['archive'])) {
                throw new RecordNotValid($type);
            }

            ApplicationTypeHistory::createNewEntry(
                Yii::$app->user->identity,
                ApplicationTypeHistory::PUT_APPLICATION_TYPE_IN_ARCHIVE,
                $id
            );
        }

        return $this->redirect(['index']);
    }

    public function actionInfo($id)
    {
        $type = ApplicationType::findOne(['id' => $id, 'archive' => false]);
        $campaign = $type->getRawCampaign()->limit(1)->one();
        return $this->render('info', ['campaign' => $campaign]);
    }

    public function actionBlock($id)
    {
        $type = ApplicationType::findOne(['id' => $id, 'archive' => false]);
        if ($type != null) {
            $type->blocked = true;
            if (!$type->save(true, ['blocked'])) {
                throw new RecordNotValid($type);
            }

            ApplicationTypeHistory::createNewEntry(
                Yii::$app->user->identity,
                ApplicationTypeHistory::BLOCK_APPLICATION_TYPE,
                $id
            );
        }

        return $this->redirect(['index']);
    }

    public function actionUnblock($id)
    {
        $type = ApplicationType::findOne(['id' => $id, 'archive' => false]);
        if ($type != null) {
            $type->blocked = false;
            if (!$type->save(true, ['blocked'])) {
                throw new RecordNotValid($type);
            }

            ApplicationTypeHistory::createNewEntry(
                Yii::$app->user->identity,
                ApplicationTypeHistory::UNBLOCK_APPLICATION_TYPE,
                $id
            );
        }

        return $this->redirect(['index']);
    }

    public function actionBlockall()
    {
        $types = ApplicationType::find()->active()->all();
        foreach ($types as $type) {
            $type->blocked = true;
            if (!$type->save(true, ['blocked'])) {
                throw new RecordNotValid($type);
            }

            ApplicationTypeHistory::createNewEntry(
                Yii::$app->user->identity,
                ApplicationTypeHistory::BLOCK_APPLICATION_TYPE,
                $type->id
            );
        }

        return $this->redirect(['index']);
    }

    public function actionUnblockall()
    {
        $types = ApplicationType::find()->active()->all();
        foreach ($types as $type) {
            $type->blocked = false;
            if (!$type->save(true, ['blocked'])) {
                throw new RecordNotValid($type);
            }

            ApplicationTypeHistory::createNewEntry(
                Yii::$app->user->identity,
                ApplicationTypeHistory::UNBLOCK_APPLICATION_TYPE,
                $type->id
            );
        }

        return $this->redirect(['index']);
    }

    public function actionHistoryChange($id)
    {
        $histories = ApplicationTypeHistory::find()
            ->where(['application_type_id' => $id])
            ->orderBy('created_at')
            ->all();

        $type = ApplicationType::findOne(['id' => $id, 'archive' => 0]);
        $campaign = $type->getRawCampaign()->one();
        return $this->render(
            'history',
            [
                'campaign' => $campaign,
                'histories' => $histories,
            ]
        );
    }
}
