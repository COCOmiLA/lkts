<?php

namespace backend\controllers;

use backend\models\RBACAuthAssignment;
use backend\models\RBACAuthItem;
use backend\models\ViewerAdmissionCampaignJunction;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;

class ViewerController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => [User::ROLE_ADMINISTRATOR]
                ]],
            ],
        ];
    }

    public function actionIndex()
    {
        $tnRBACAuthAssignment = RBACAuthAssignment::tableName();
        $tnViewerAdmissionCampaignJunction = ViewerAdmissionCampaignJunction::tableName();
        $tnUser = User::tableName();

        $dataProvider = new ActiveDataProvider([
            'query' => (new Query())
                ->from($tnRBACAuthAssignment)
                ->distinct()
                ->select([
                    "{$tnRBACAuthAssignment}.user_id",
                    "{$tnUser}.username",
                    "{$tnUser}.id",
                ])
                ->leftJoin(
                    $tnViewerAdmissionCampaignJunction,
                    "{$tnRBACAuthAssignment}.user_id = {$tnViewerAdmissionCampaignJunction}.user_id"
                )
                ->leftJoin(
                    $tnUser,
                    "{$tnUser}.id = {$tnRBACAuthAssignment}.user_id"
                )
                ->where(["{$tnRBACAuthAssignment}.item_name" => RBACAuthItem::VIEWER])
                ->orderBy("{$tnUser}.id"),
        ]);

        return $this->render(
            'index',
            ['dataProvider' => $dataProvider]
        );
    }

    public function actionView($id)
    {
        $user = User::findOne($id);

        $applicationTypess = ApplicationType::find()
            ->active()
            ->all();

        $admissionCampaignJunctions = ViewerAdmissionCampaignJunction::find()
            ->select('application_type_id')
            ->where(['user_id' => $id])
            ->column();

        return $this->render(
            'view',
            [
                'user' => $user,
                'applicationTypes' => $applicationTypess,
                'admissionCampaignJunctions' => $admissionCampaignJunctions,
            ]
        );
    }

    public function actionUpdate($id)
    {
        $user = User::findOne($id);

        if (Yii::$app->request->isPost) {
            $formName = (new ViewerAdmissionCampaignJunction())->formName();

            $admissionCampaignJunctionsInDb = ViewerAdmissionCampaignJunction::find()
                ->select('application_type_id')
                ->where(['user_id' => $id])
                ->column();

            $data = Yii::$app->request->post($formName);
            $admissionCampaignJunctionsInPost = [];
            foreach ($data as $admissionCampaignJunction) {
                $postAtId = intval($admissionCampaignJunction['application_type_id']);
                if (!empty($postAtId)) {
                    $admissionCampaignJunctionsInPost[] = $postAtId;
                }
            }

            $toDelete = array_diff($admissionCampaignJunctionsInDb, $admissionCampaignJunctionsInPost);
            $toSave = array_diff($admissionCampaignJunctionsInPost, $admissionCampaignJunctionsInDb);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($toDelete as $idDelete) {
                    $admissionCampaignJunctions = ViewerAdmissionCampaignJunction::findAll([
                        'application_type_id' => $idDelete,
                        'user_id' => $id
                    ]);
                    foreach ($admissionCampaignJunctions as $admissionCampaignJunction) {
                        $admissionCampaignJunction->delete();
                    }
                }

                foreach ($toSave as $atId) {
                    $admissionCampaignJunction = new ViewerAdmissionCampaignJunction();
                    $admissionCampaignJunction->user_id = $id;
                    $admissionCampaignJunction->application_type_id = $atId;
                    if (!$admissionCampaignJunction->save()) {
                        throw new RecordNotValid($admissionCampaignJunction);
                    }
                }

                $transaction->commit();
            } catch (Throwable $th) {
                Yii::error(
                    "Ошибка во обработке разрешений на просмотр заявлений для роли  «зритель»: {$th->getMessage()}",
                    'ViewerController.actionUpdate'
                );

                $transaction->rollBack();
            }

            return $this->redirect('index');
        }

        $applicationTypes = ApplicationType::find()
            ->active()
            ->all();

        $admissionCampaignJunctions = ViewerAdmissionCampaignJunction::find()
            ->select('application_type_id')
            ->where(['user_id' => $id])
            ->column();

        return $this->render(
            'update',
            [
                'user' => $user,
                'applicationTypes' => $applicationTypes,
                'admissionCampaignJunctions' => $admissionCampaignJunctions,
            ]
        );
    }
}
