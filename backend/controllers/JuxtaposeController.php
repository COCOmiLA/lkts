<?php

namespace backend\controllers;

use backend\models\search\UserDuplesSearchModel;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class JuxtaposeController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['administrator']
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(int $user_id)
    {
        $user = User::findOne($user_id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }
        $search_model = (new UserDuplesSearchModel())->load_and_search(\Yii::$app->request->post());
        return $this->render('index', compact('search_model', 'user'));
    }

    public function actionUnbindFromUser(int $user_id)
    {
        $user = User::findOne($user_id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }
        $user->guid = null;
        $user->user_ref_id = null;
        $user->save(false, ['guid', 'user_ref_id']);
        Yii::$app->session->setFlash('alert', [
            'body' => "Пользователь {$user->getPublicIdentity()} успешно отвязан от физического лица в Информационной системе вуза",
            'options' => ['class' => 'alert-success']
        ]);
        return $this->redirect(['index', 'user_id' => $user->id]);
    }

    public function actionBindToUser(int $user_id)
    {
        $abit_code = Yii::$app->request->post('abit_code');
        if (!$abit_code) {
            throw new BadRequestHttpException('Не удалось получить данные для сопоставления');
        }
        $user = User::findOne($user_id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }
        $referenceType = UserReferenceTypeManager::getUserReferenceFrom1CByGuid($abit_code);
        if ($referenceType) {
            $user->guid = $referenceType->reference_id;
            $user->user_ref_id = $referenceType->id;
            if ($user->save()) {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Пользователь успешно сопоставлен с физ. лицом в Информационной системе вуза',
                    'options' => ['class' => 'alert-success']
                ]);
            }
        }
        return $this->redirect(['index', 'user_id' => $user->id]);
    }
}