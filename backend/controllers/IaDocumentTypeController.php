<?php

namespace backend\controllers;

use common\models\dictionary\DocumentType;
use common\models\IndividualAchievementDocumentType;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

class IaDocumentTypeController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post']
                ]
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

    protected function loadModel(IndividualAchievementDocumentType $model, array $post)
    {
        if ($model->load($post)) {
            $docType = $model->getDocumentTypeRef()->one();
            $model->document_type = $docType->code;
            $model->campaign_code = $model->getAdmissionCampaignRef()->one()->reference_id;
            $model->archive = false;
            return $model->save();
        }
        return false;
    }

    public function actionCreate()
    {
        $model = new IndividualAchievementDocumentType(['archive' => false]);
        if (Yii::$app->request->isPost) {
            if ($this->loadModel($model, Yii::$app->request->post())) {
                return $this->redirect(['/scan/index']);
            }
        }

        $tnDocumentType = DocumentType::tableName();
        return $this->render(
            'create',
            [
                'model' => $model,
                'document_types' => ArrayHelper::map(
                    DocumentType::find()
                        ->notMarkedToDelete()
                        ->active()
                        ->orderBy("{$tnDocumentType}.description")
                        ->all(),
                    'id',
                    'description'
                ),
                'campaigns' => ArrayHelper::map(
                    AdmissionCampaign::find()
                        ->active()
                        ->all(),
                    'ref_id',
                    'name'
                )
            ]
        );
    }

    public function actionUpdate($id)
    {
        $model = IndividualAchievementDocumentType::findOne($id);
        if (Yii::$app->request->isPost) {
            if ($this->loadModel($model, Yii::$app->request->post())) {
                return $this->redirect(['/scan/index']);
            }
        }

        $tnDocumentType = DocumentType::tableName();
        return $this->render(
            'create',
            [
                'model' => $model,
                'document_types' => ArrayHelper::map(
                    DocumentType::find()
                        ->notMarkedToDelete()
                        ->active()
                        ->orderBy("{$tnDocumentType}.description")
                        ->all(),
                    'id',
                    'description'
                ),
                'campaigns' => ArrayHelper::map(
                    AdmissionCampaign::find()
                        ->active()
                        ->all(),
                    'ref_id',
                    'name'
                )
            ]
        );
    }

    public function actionDelete($id)
    {
        $model = IndividualAchievementDocumentType::findOne($id);
        if (isset($model)) {
            $model->archive();
        }
        return $this->redirect(Url::toRoute('scan/index'), 302);
    }
}
