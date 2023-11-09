<?php

namespace backend\controllers;

use backend\models\RBACAuthAssignment;
use backend\models\search\UserSearch;
use backend\models\UserForm;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\PersonalData;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;




class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
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
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'searchModel' => $searchModel,
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
        $model = new UserForm();
        $model->setScenario('create');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        $app_roles = Yii::$app->authManager->getRoles();
        $roles = [];
        $roles['administrator'] = $app_roles['administrator'];
        $roles['manager'] = $app_roles['manager'];
        $roles['viewer'] = $app_roles['viewer'];
        return $this->render('create', [
            'model' => $model,
            'roles' => ArrayHelper::map($roles, 'name', 'name')
        ]);
    }

    private function renderUsers($error_message = null)
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->renderAjax(
            "../user/user_partial/user_grid",
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'error_message' => $error_message
            ]
        );
    }

    public function actionMoveToArchive()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $postArray = $request->post('arrayData');
            foreach ($postArray as $value) {
                $model = User::findOne(['id' => (int)$value]);
                if (!empty($model)) {
                    $model->is_archive = true;
                    $model->save();
                }
            }
            return $this->renderUsers();
        }
        return null;
    }

    public function actionMoveFromArchive()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $postArray = $request->post('arrayData');
            foreach ($postArray as $value) {
                $model = User::find()->andWhere(['id' => (int)$value])->limit(1)->one();
                if (!empty($model)) {
                    $model->is_archive = false;
                    $model->save();
                }
            }
            return $this->renderUsers();
        }
        return null;
    }

    public function actionDeleteUsers()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $error_msg = null;
            $postArray = $request->post('arrayData');
            $transaction = User::getDb()->beginTransaction();
            try {
                foreach ($postArray as $value) {
                    $model = User::find()->andWhere(['id' => (int)$value])->limit(1)->one();
                    if (!is_null($model)) {
                        $model->delete();
                    }
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $error_msg = $e->getMessage();
            }
            return $this->renderUsers();
        }
        return false;
    }

    public function actionDeleteAllUsers()
    {
        $admin_and_moderator_ids = RBACAuthAssignment::find()
            ->select(['user_id'])
            ->where(['item_name' => ['administrator', 'manager']],);
        $userArrayBatches = User::find()
            ->where(['not', ['id' => $admin_and_moderator_ids]])
            ->andWhere(['not', ['username' => ['webmaster', 'manager']]])
            ->batch();
        $failed = false;
        foreach ($userArrayBatches as $userArray) {
            foreach ($userArray as $user) {
                if (!$user->delete()) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => "Ошибка удаления пользователя с ID = {$user->id}",
                        'options' => ['class' => 'alert-danger']
                    ]);
                    $failed = true;
                    break;
                }
            }
            if ($failed) {
                break;
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    private function getRandomWord($len)
    {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode('', $word), 0, $len);
    }

    public function actionDepersonalizeUsers()
    {
        $request = Yii::$app->request;
        $error_message = null;
        if ($request->isAjax) {
            $postArray = $request->post('arrayData');
            foreach ($postArray as $value) {
                $model = User::find()->andWhere(['id' => (int)$value])->limit(1)->one();
                if ($model !== null) {
                    $profile = $model->userProfile;
                    $profile->firstname = $this->getRandomWord(mb_strlen((string)$profile->firstname));
                    $profile->middlename = $this->getRandomWord(mb_strlen((string)$profile->middlename));
                    $profile->lastname = $this->getRandomWord(mb_strlen((string)$profile->lastname));
                    $profile->passport_series = $this->getRandomWord(mb_strlen((string)$profile->passport_series));
                    $profile->passport_number = $this->getRandomWord(mb_strlen((string)$profile->passport_number));

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $profile->save(false);
                        $questionaries = AbiturientQuestionary::find()->andWhere(['user_id' => $model->id])->all();
                        foreach ($questionaries as $questionary) {
                            $personalData = $questionary->personalData;
                            $personalData->setScenario(PersonalData::SCENARIO_DEPERSONALIZATION);
                            $personalData->firstname = $this->getRandomWord(mb_strlen((string)$personalData->firstname));
                            $personalData->middlename = $this->getRandomWord(mb_strlen((string)$personalData->middlename));
                            $personalData->lastname = $this->getRandomWord(mb_strlen((string)$personalData->lastname));
                            $personalData->passport_series = $this->getRandomWord(mb_strlen((string)$personalData->passport_series));
                            $personalData->passport_number = $this->getRandomWord(mb_strlen((string)$personalData->passport_number));
                            $personalData->snils = null;

                            $personalData->save(false);

                            $passportData = $questionary->passportData;
                            foreach ($passportData as $passportItem) {
                                $passportItem->issued_by = $this->getRandomWord(mb_strlen((string)$passportItem->issued_by));
                                $passportItem->issued_date = null;
                                $passportItem->series = $this->getRandomWord(mb_strlen((string)$passportItem->series));
                                $passportItem->number = $this->getRandomWord(mb_strlen((string)$passportItem->number));

                                $passportItem->save(false);
                            }
                        }
                        $transaction->commit();
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        $error_message = $e->getMessage();
                    }
                }
            }
            return $this->renderUsers($error_message);
        }
        return null;
    }


    




    public function actionUpdate($id)
    {
        $model = new UserForm();
        $model->setModel($this->findModel($id));
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'id' => $id,
            'model' => $model,
            'roles' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name')
        ]);
    }

    





    public function actionDelete(int $id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->findModel($id)->delete();
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка удаления пользователя: {$e->getMessage()}");
            Yii::$app->session->setFlash('alert', [
                'body' => 'Ошибка удаления пользователя',
                'options' => ['class' => 'alert-danger']
            ]);
        }

        return $this->redirect(['index']);
    }

    






    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionRemoveApplicationsBlocking()
    {
        $request = Yii::$app->request;
        $error_message = null;
        if ($request->isAjax) {
            $postArray = $request->post('arrayData');
            $tnUser = User::tableName();
            $tnBachelorApplication = BachelorApplication::tableName();
            $users = User::find()
                ->joinWith(['rawApplications'])
                ->andWhere(['IN', "{$tnUser}.id", $postArray])
                ->andWhere(["{$tnBachelorApplication}.block_status" => BachelorApplication::BLOCK_STATUS_ENABLED,])
                ->all();
            if (!$users) {
                $error_message = 'Список пустой';
                return $this->renderUsers($error_message);
            }
            foreach ($users as $user) {
                

                
                $apps = $user->getRawApplications()
                    ->andWhere(["{$tnBachelorApplication}.block_status" => BachelorApplication::BLOCK_STATUS_ENABLED,])
                    ->all();
                if (!$apps) {
                    continue;
                }

                foreach ($apps as $app) {
                    

                    $app->block_status = BachelorApplication::BLOCK_STATUS_DISABLED;
                    if (!$app->save()) {
                        $error_message = 'Ошибка снятия блокировки';

                        return $this->renderUsers($error_message);
                    }
                }
            }
        }
        return $this->renderUsers(null);
    }

    




    public function actionMergeIndividual(int $id)
    {
        $user = User::findOne($id);

        if (Yii::$app->request->isPost && $user->load(Yii::$app->request->post())) {
            $user->guid = ArrayHelper::getValue(Yii::$app->request->post(), "{$user->formName()}.guid");

            $userReference = UserReferenceTypeManager::getUserReferenceFrom1C($user);
            $user->user_ref_id = $userReference->id ?? null;

            $transaction = Yii::$app->db->beginTransaction();
            if ($user->save(false, ['user_ref_id', 'guid'])) {
                $transaction->commit();
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::t('backend', 'Пользователь успешно объединен'),
                    'options' => ['class' => 'alert-success']
                ]);

                return $this->redirect(['/user/index']);
            }

            $transaction->rollBack();
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Ошибка обновления пользователя'),
                'options' => ['class' => 'alert-danger']
            ]);
        }

        return $this->render(
            'merge_individual',
            ['user' => $user]
        );
    }
}
