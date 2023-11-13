<?php

namespace backend\controllers;

use backend\models\SortedElementPage;
use common\models\User;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class PageController extends Controller
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

    public function actionIndex($role = '')
    {
        SortedElementPage::updateElements($role);
        $roleName = User::getRoleName(ucfirst($role));

        $model = new SortedElementPage();
        $model->sortablePageElements = '';
        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->load(Yii::$app->request->post(), $role);

                $transaction->commit();
            } catch (Throwable $th) {
                Yii::error(
                    "Ошибка при сохранении списка элементов для роли «{$role}», по причине: {$th->getMessage()}",
                    'PageController.actionIndex'
                );

                $transaction->rollBack();
                throw $th;
            }

            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => "Изменения элементов страницы '{$roleName}', прошло успешно."
            ]);
        }

        return $this->render(
            'index',
            [
                'role' => $role,
                'model' => $model,
                'roleName' => $roleName
            ]
        );
    }
}
