<?php

namespace backend\controllers;

use backend\models\CleanLog;
use common\models\User;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;






class CleanerController extends Controller
{
    private const DELETE_LIMIT = 10000;

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

    


    public function actionIndex()
    {
        return $this->render('index');
    }

    


    public function actionDeleteLogs()
    {
        $model = new CleanLog();
        if (
            Yii::$app->request->isPost &&
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $hasError = false;
            try {
                $transaction = Yii::$app->db->beginTransaction();

                if (Yii::$app->db->driverName === 'pgsql') {
                    $tn = $model->className::tableName();
                    $deleteQuery = "
                        DELETE FROM {$tn}
                        WHERE ctid IN (
                            SELECT ctid
                            FROM {$tn}
                            ORDER BY \"id\"
                            LIMIT :delete_limit
                        )
                    ";

                    $deletedCount = 0;
                    $forCount = floor($model->numberToDelete / CleanerController::DELETE_LIMIT);
                    for ($I = 0; $I < $forCount; $I++) {
                        $deletedCount += Yii::$app->db
                            ->createCommand($deleteQuery, ['delete_limit' => CleanerController::DELETE_LIMIT])
                            ->execute();
                    }
                    $deletedCount += Yii::$app->db
                        ->createCommand($deleteQuery, ['delete_limit' => $model->numberToDelete - CleanerController::DELETE_LIMIT * $forCount])
                        ->execute();
                } else {
                    $deletedCount = $model->className::deleteAll([
                        'IN',
                        'id',
                        $model->className::find()
                            ->select('id')
                            ->orderBy(['id' => SORT_ASC])
                            ->limit($model->numberToDelete)
                            ->column()
                    ]);
                }

                if ($deletedCount == $model->numberToDelete) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t(
                            'backend',
                            'Из <strong>{tableName}</strong> было успешно удалено {number} записей',
                            [
                                'tableName' => $model->tableName,
                                'number' => $model->numberToDelete,
                            ]
                        ),
                        'options' => ['class' => 'alert-success']
                    ]);
                } else {
                    $hasError = true;
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t(
                            'backend',
                            'Произошла ошибка удаления {number} записей из <strong>{tableName}</strong>',
                            [
                                'tableName' => $model->tableName,
                                'number' => $model->numberToDelete,
                            ]
                        ),
                        'options' => ['class' => 'alert-danger']
                    ]);
                }
            } catch (Throwable $th) {
                $hasError = true;
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::t(
                        'backend',
                        'Возникла системная ошибка. Обратитесь к администратору.'
                    ),
                    'options' => ['class' => 'alert-danger']
                ]);
                Yii::error("Ошибка во время удаления логов, по причине: {$th->getMessage()}", 'actionDeleteLogs');
            }
            if ($hasError) {
                $transaction->rollBack();
            }
        }

        return $this->render('index');
    }
}
