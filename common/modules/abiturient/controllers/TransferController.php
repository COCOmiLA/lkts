<?php








namespace common\modules\abiturient\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class TransferController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['came-back'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'actions' => ['transfer'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    public function actionTransfer($id)
    {
        $originalId = Yii::$app->user->getId();
        $user = User::findOne($id);
        if ($originalId && $user) {
            Yii::$app->user->switchIdentity($user, 0);
            Yii::$app->session->set('transfer', $originalId);
        }

        return $this->redirect('/');
    }

    public function actionCameBack()
    {
        $session = Yii::$app->session;
        if ($session->has('transfer')) {
            $originalId = $session->get('transfer');
            $user = User::findOne($originalId);
            if ($originalId && $user) {
                Yii::$app->user->switchIdentity($user, 0);
                $session->remove('transfer');
            }
        }

        return $this->redirect('/');
    }
}
