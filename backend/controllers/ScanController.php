<?php

namespace backend\controllers;

use common\helpers\Functions;
use common\models\Attachment;
use common\models\AttachmentArchive;
use common\models\AttachmentType;
use common\models\AttachmentTypeTemplate;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;
use common\models\errors\RecordNotValid;
use common\models\IndividualAchievementDocumentType;
use common\models\query\ActiveRecordDataProvider;
use common\models\settings\CodeSetting;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class ScanController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'restore' => ['post']
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

    public function actionDownload($id)
    {
        $attachment = Attachment::findOne((int)$id);
        if ($attachment != null) {
            $path = $attachment->getAbsPath();
            if ($path && file_exists($path)) {
                return Yii::$app->response->sendFile($path);
            } else {
                Yii::$app->session->setFlash('fileErorr', 'Отсутствует файл');
                return $this->redirect('/admin/scan/index', 302);
            }
        }
        throw new NotFoundHttpException();
    }


    public function actionDeletefile($id)
    {
        $attachment = Attachment::findOne((int)$id);
        if ($attachment != null) {
            $attachment->delete();
        }

        if (Yii::$app->request->referrer) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->goHome();
        }
    }

    public function actionSolveConflict($id)
    {
        $newType = Yii::$app->request->post('type');
        foreach (Attachment::findAll(['attachment_type_id' => $id]) as $item) {
            $history = new AttachmentArchive();
            $arr = $item->toArray();
            $history->attributes = $arr;
            $history->attachment_id = $item->id;
            $history->save();
        }
        Attachment::updateAll(['attachment_type_id' => $newType], ['attachment_type_id' => $id]);
        return $this->redirect('/admin/scan/index', 302);
    }

    public function actionIndex()
    {
        $model = CodeSetting::findOne(['name' => 'scan_sort_code']);

        $scansQuery = AttachmentType::find()
            ->where([
                'from1c' => [null, false],
                'system_type' => AttachmentType::SYSTEM_TYPE_COMMON
            ])
            ->orderBy('custom_order');
        $scansDataProvider = new ActiveDataProvider([
            'query' => $scansQuery
        ]);
        $iaQuery = IndividualAchievementDocumentType::find()
            ->joinWith(['campaign', 'availableDocumentTypeFilterRef'])
            ->where([IndividualAchievementDocumentType::tableName() . '.archive' => false])
            ->orderBy(IndividualAchievementDocumentType::tableName() . '.custom_order');
        $iaDataProvider = new ActiveRecordDataProvider([
            'primary_column' => IndividualAchievementDocumentType::tableName() . '.id',
            'query' => $iaQuery,
        ]);
        $iaDataProvider->sort->attributes['campaign.name'] = [
            'asc' => [
                AdmissionCampaign::tableName() . '.name' => SORT_ASC,
            ],
            'desc' => [
                AdmissionCampaign::tableName() . '.name' => SORT_DESC,
            ],
        ];
        $iaDataProvider->sort->attributes['availableDocumentTypeFilterRef.0.reference_name'] = [
            'asc' => [
                StoredAvailableDocumentTypeFilterReferenceType::tableName() . '.reference_name' => SORT_ASC,
            ],
            'desc' => [
                StoredAvailableDocumentTypeFilterReferenceType::tableName() . '.reference_name' => SORT_DESC,
            ],
        ];
        
        $conflicts = Attachment::find()
            ->leftJoin('attachment_type', 'attachment_type.id = attachment.attachment_type_id')
            ->where(['attachment_type.id' => null]);
        
        $conflictsPKs = ArrayHelper::getColumn($conflicts->all(), 'application.type.campaign');
        $conflictsPKs = ArrayHelper::map($conflictsPKs, 'referenceType.reference_uid', 'name');
        return $this->render("index", [
            'scansDataProvider' => $scansDataProvider,
            'conflicts' => $conflicts,
            'conflictsPKs' => $conflictsPKs,
            'admissionCampaigns' => AdmissionCampaign::find()->with('referenceType')->where(['archive' => false])->all(),
            'iaDocTypes' => $iaDataProvider,
            'orderValue' => $model->value,
        ]);
    }

    public function actionSetScanSort()
    {
        $orderValue = Yii::$app->request->post('order', null);
        if ($orderValue === null) {
            return $this->redirect(Yii::$app->request->referrer);
        }
        $model = CodeSetting::findOne(['name' => 'scan_sort_code']);
        $model->value = $orderValue;
        if (!$model->save()) {
            throw new RecordNotValid($model);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionSortScanCopys()
    {
        $request = Yii::$app->request;
        $counter = 1;
        if ($request->isAjax) {
            try {
                $post = $request->post('arrayData');
                $postArray = explode(',', $post);
                foreach ($postArray as $value) {
                    $model = AttachmentType::findOne(['id' => (int)$value]);
                    $model->custom_order = $counter++;
                    if (!$model->save()) {
                        throw new RecordNotValid($model);
                    }
                }
            } catch (\Throwable $e) {
                throw new HttpException('500', 'Данные не сохранены');
            }
        }
    }

    public function actionSortIaScanCopys()
    {
        $request = Yii::$app->request;
        $counter = 1;
        if ($request->isAjax) {
            try {
                $post = $request->post('arrayData');
                $postArray = explode(',', $post);
                foreach ($postArray as $value) {
                    $model = IndividualAchievementDocumentType::findOne(['id' => (int)$value]);
                    $model->custom_order = $counter++;
                    if (!$model->save()) {
                        throw new RecordNotValid($model);
                    }
                }
            } catch (\Throwable $e) {
                throw new HttpException('500', 'Данные не сохранены');
            }
        }
    }

    public function actionCreate()
    {
        return $this->createOrUpdateProcessor('create');
    }

    


    private function processDocumentTypeSave(AttachmentType $attachmentType)
    {
        if (!empty($attachmentType->document_type_guid)) {
            $docType = DocumentType::findOne(['ref_key' => $attachmentType->document_type_guid]);
            if (!is_null($docType)) {
                $attachmentType->document_type_id = $docType->id;
                if ($attachmentType->validate(['document_type_id'])) {
                    $attachmentType->save(false, ['document_type_id']);
                }
            }
        }
    }

    public function actionRestore(int $scan_id)
    {
        $attachmentType = AttachmentType::findOne($scan_id);
        if (!$attachmentType) {
            throw new NotFoundHttpException('Не найдено');
        }
        $attachmentType->hidden = false;
        $attachmentType->save(true, ['hidden']);
        return $this->redirect(['index']);
    }

    public function actionUpdate(int $id)
    {
        return $this->createOrUpdateProcessor('update', $id);
    }

    private function getDocumentTypeList(): array
    {
        $tnDocumentType = DocumentType::tableName();
        return ArrayHelper::map(
            DocumentType::find()
                ->notMarkedToDelete()
                ->active()
                ->orderBy("{$tnDocumentType}.description")
                ->all(),
            'ref_key',
            'description'
        );
    }

    public function actionDelete($id)
    {
        $type = AttachmentType::findOne($id);
        $result = true;
        if (isset($type)) {
            $result = $type->delete();
        }
        if (!$result) {
            if ($type->system_type == AttachmentType::SYSTEM_TYPE_COMMON && $type->hide()) {
                Yii::$app->session->setFlash('scans-msg', 'Не удалось удалить тип скан-копии так как к ней поступающими приложены файлы, тип скан-копии был помечен как скрытый');
            } else {
                Yii::$app->session->setFlash('scans-error', 'Не удалось удалить тип скан-копии так как к ней поступающими приложены файлы');
            }
        }
        return $this->redirect(Url::toRoute('scan/index'), 302);
    }

    private function createOrUpdateProcessor(string $viewPath, ?int $id = null)
    {
        $model = new AttachmentType();
        if ($id) {
            $model = AttachmentType::findOne($id);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->processDocumentTypeSave($model);
                $this->processAttachmentTypeTemplate($model);
                return $this->redirect(['index']);
            }
            if ($model->errors) {
                $flatten_errors = implode(', ', Functions::array_flatten($model->errors));
                Yii::$app->session->setFlash('alert', [
                    'body' => "Не удалось сохранить изменения: {$flatten_errors}",
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }

        $entities = AttachmentType::GetRelatedList(!!$model->from1c);
        return $this->render(
            $viewPath,
            [
                'model' => $model,
                'entities' => $entities,
                'document_types' => $this->getDocumentTypeList()
            ]
        );
    }

    




    private function processAttachmentTypeTemplate(AttachmentType $attachmentType): void
    {
        $attachmentTypeWithLoadedPost = $attachmentType->getOrBuildAttachmentTypeTemplate();
        $attachmentTypeWithLoadedPost->user_id = Yii::$app->user->identity->id;
        $attachmentTypeWithLoadedPost->uploadFromPost();
    }

    public function actionSystemScansTemplate()
    {
        $scansQuery = AttachmentType::find()
            ->where(['!=', 'system_type', AttachmentType::SYSTEM_TYPE_COMMON])
            ->orderBy('custom_order');
        $scansDataProvider = new ActiveDataProvider([
            'query' => $scansQuery
        ]);

        return $this->render(
            'system-scans-template',
            ['scansDataProvider' => $scansDataProvider]
        );
    }

    public function actionSystemScansTemplateUpdate(int $id)
    {
        $tnAttachmentType = AttachmentType::tableName();
        $tnAttachmentTypeTemplate = AttachmentTypeTemplate::tableName();
        $model = AttachmentTypeTemplate::find()
            ->joinWith('attachmentType')
            ->andWhere(['!=', "{$tnAttachmentType}.system_type", AttachmentType::SYSTEM_TYPE_COMMON])
            ->andWhere(["{$tnAttachmentTypeTemplate}.attachment_type_id" => $id])
            ->one();
        if (!$model) {
            $model = new AttachmentTypeTemplate();
            $model->attachment_type_id = $id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->identity->id;
            $model->uploadFromPost();

            return $this->redirect(['system-scans-template']);
        }

        return $this->render(
            'system-scans-template-update',
            ['model' => $model]
        );
    }
}
