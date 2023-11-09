<?php

namespace backend\controllers;

use common\components\RegulationRelationManager;
use common\models\AttachmentType;
use common\models\Regulation;
use common\models\User;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;




class RegulationController extends Controller
{
    public function behaviors()
    {
        return [
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
        $dataProvider = new ActiveDataProvider([
            'query' => Regulation::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    





    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    




    public function actionCreate()
    {
        $model = new Regulation();
        $transaction = Yii::$app->db->beginTransaction();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->isFileContent()) {
                $model->file = UploadedFile::getInstance($model, 'file');
                if ($model->upload()) {
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } elseif ($model->save()) {
                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $transaction->rollBack();
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    






    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->isFileContent()) {
                $model->file = UploadedFile::getInstance($model, 'file');
            }
            if ($model->validate()) {
                $savingStatus = true;
                if ($model->isFileContent() && $model->file !== null) {
                    $model->file = UploadedFile::getInstance($model, 'file');
                    $savingStatus = $model->upload();
                } else {
                    $model->save(false);
                }
                if ($savingStatus) {
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        $transaction->rollBack();
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    











    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $confirmed = Yii::$app->request->post('confirmed');
        if ($model->getUserRegulations()->exists()) {
            if (!$confirmed) {
                Yii::$app->session->setFlash('confirm_delete', [
                    'id' => $id,
                    'name' => $model->name
                ]);
            } else {
                $transaction = Yii::$app->db->beginTransaction();
                if (!isset($transaction)) {
                    throw new UserException('Невозможно создать транзакцию.');
                }
                try {
                    foreach ($model->userRegulations as $userRegulation) {
                        $attachments = $userRegulation->attachments;
                        foreach ($attachments as $attachment) {
                            $attachment->silenceSafeDelete();
                        }
                        if (!$userRegulation->delete()) {
                            throw new UserException('Невозможно удалить запись подтверждения о прочтении пользователем нормативного документа.');
                        }
                    }
                    $type = $model->attachmentType;
                    if ($type !== null) {
                        $type->hidden = true;
                        $type->save();
                    }
                    if (!$model->delete()) {
                        throw new UserException('Невозможно удалить нормативный документ.');
                    }
                    $transaction->commit();
                } catch (Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        } else {
            $model->delete();
        }
        return $this->redirect(['index']);
    }

    






    protected function findModel($id)
    {
        if (($model = Regulation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetTypes($id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->isPost && isset($_POST['depdrop_parents'])) {
            $types = [];
            $param = $_POST['depdrop_parents'][0];

            if (!$param) {
                return ['output' => [], 'selected' => ''];
            }

            if (in_array($param, [
                RegulationRelationManager::RELATED_ENTITY_PREFERENCE,
                RegulationRelationManager::RELATED_ENTITY_OLYMPIAD,
                RegulationRelationManager::RELATED_ENTITY_TARGET_RECEPTION
            ], true)) {
                $typesToAdd = AttachmentType::GetCommonAttachmentTypesQuery()->all();
            } else {
                $typesToAdd = AttachmentType::GetCommonAttachmentTypesQuery($param)->all();
            }

            $typesToAdd = array_map(function ($el) {
                return [
                    'name' => $el->name,
                    'id' => $el->id
                ];
            }, $typesToAdd);
            $types = array_merge($types, $typesToAdd);
            $selected = '';
            if ($id !== null) {
                $model = Regulation::findOne($id);
                $selected = $model === null ?: $model->attachment_type;
            }
            return ['output' => $types, 'selected' => $selected];
        }
        return ['output' => [], 'selected' => ''];
    }

    public function actionDownloadRegulationFile($id)
    {
        $regulation = Regulation::findOne((int)$id);
        if ($regulation === null || !$regulation->isFileContent()) {
            throw new NotFoundHttpException();
        }
        $regulation_path = $regulation->getAbsPath();
        if ($regulation_path && file_exists($regulation_path)) {
            return Yii::$app->response->sendFile($regulation_path, null, ['mimeType' => $regulation->getMimeType(), 'inline' => $regulation->content_file_extension === 'pdf']);
        }

        throw new UserException("Невозможно получить файл \"{$regulation->name}\". Обратитесь к администратору.");
    }
}
