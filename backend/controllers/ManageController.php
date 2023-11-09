<?php








namespace backend\controllers;

use backend\components\ReportsPreprocessor;
use backend\components\XLSXWriter;
use backend\models\ManageAC;
use backend\models\ManagerAllowChat;
use backend\models\ManagerNotificationsConfigurator;
use backend\models\RBACAuthAssignment;
use backend\models\RBACAuthItem;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDirectionReferenceType;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\ModerateHistory;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ManageController extends Controller
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

    public function actionModerateReport()
    {
        $model = new DynamicModel(['username', 'campaign', 'timeStart', 'timeStop']);
        $model->addRule(
            [
                'username',
                'campaign',
                'timeStart',
                'timeStop'
            ],
            'string'
        );

        $tnUser = User::tableName();
        $tnModerateHistory = ModerateHistory::tableName();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $query = ModerateHistory::getModerateQuery(
                $model->username,
                $model->campaign,
                $model->timeStart,
                $model->timeStop
            );
            $query = $query
                ->select(["{$tnModerateHistory}.user_id", "{$tnUser}.username", "{$tnUser}.id"])
                ->distinct();
        } else {

            $query = (new Query())
                ->from("{$tnModerateHistory}")
                ->select(["{$tnModerateHistory}.user_id", "{$tnUser}.username", "{$tnUser}.id"])
                ->distinct()
                ->innerJoin("{$tnUser}", "{$tnUser}.id = {$tnModerateHistory}.user_id")
                ->orderBy("{$tnUser}.id");
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 100]
        ]);
        $applicationType = ApplicationType::find()->all();
        $applicationTypeArray = [];
        if (!empty($applicationType)) {
            $applicationTypeArray = ArrayHelper::map($applicationType, 'campaign_id', 'name');
        }

        $moderateName = User::findActive()
            ->andWhere([
                'in',
                'id',
                RBACAuthAssignment::find()
                    ->select(['user_id'])
                    ->where(['in', 'item_name', ['manager', 'administrator']])
            ])
            ->all();
        $moderateNameArray = [];
        if (!empty($moderateName)) {
            $moderateNameArray = ArrayHelper::map($moderateName, 'username', 'username');
        }

        return $this->render(
            'moderate_report',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'moderateNameArray' => $moderateNameArray,
                'applicationTypeArray' => $applicationTypeArray,
            ]
        );
    }

    public function actionReport()
    {
        $model = new DynamicModel(['username', 'campaign', 'timeStart', 'timeStop']);
        $model->addRule(
            [
                'username',
                'campaign',
                'timeStart',
                'timeStop'
            ],
            'string'
        );

        $tnUser = User::tableName();
        $thModerateHistory = ModerateHistory::tableName();
        $thRBACAuthAssignment = RBACAuthAssignment::tableName();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $query = ModerateHistory::getModerateQuery(
                $model->username,
                $model->campaign,
                $model->timeStart,
                $model->timeStop
            );
        } else {
            $query = ModerateHistory::find()
                ->innerJoin("{$tnUser}", "{$tnUser}.id = {$thModerateHistory}.user_id")
                ->orderBy("{$tnUser}.id");
        }
        $query = $query
            ->distinct()
            ->select(["{$thModerateHistory}.user_id", "{$tnUser}.username", "{$tnUser}.id"])
            ->leftJoin(
                $thRBACAuthAssignment,
                "{$thModerateHistory}.user_id = {$thRBACAuthAssignment}.user_id"
            )
            ->andWhere(["{$thRBACAuthAssignment}.item_name" => User::ROLE_MANAGER]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 100]
        ]);
        $applicationType = ApplicationType::find()->all();
        $applicationTypeArray = [];
        if (!empty($applicationType)) {
            $applicationTypeArray = ArrayHelper::map($applicationType, 'campaign_id', 'name');
        }

        $moderateName = User::findActive()
            ->andWhere([
                'in',
                'id',
                RBACAuthAssignment::find()
                    ->select(['user_id'])
                    ->where(['in', 'item_name', [User::ROLE_MANAGER]])
            ])
            ->all();
        $moderateNameArray = [];
        if (!empty($moderateName)) {
            $moderateNameArray = ArrayHelper::map($moderateName, 'username', 'username');
        }

        return $this->render(
            'report',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'moderateNameArray' => $moderateNameArray,
                'applicationTypeArray' => $applicationTypeArray,
            ]
        );
    }

    public function actionSpecReport($type = '')
    {
        $label = [
            'from1C' => 'Наличие в 1С',
            'campaign_code' => 'Код ПК',
            'speciality_code' => 'Код НП',
            'applications_count' => 'Количество',
            'abit_status' => 'Статус заявления',
            'abit_draft_status' => 'Состояние черновика',
            'speciality_name' => 'Наименование НП',
        ];
        $userQuery = User::find()
            ->select(['id', '(user_ref_id is not null) AS from1C']);
        $subBachelorApplicationQuery = BachelorApplication::find()
            ->select(['user_id', 'MAX(draft_status) AS max_draft_status'])
            ->where(['archive' => false])
            ->groupBy('user_id');
        $query = (new Query())
            ->select([
                StoredDirectionReferenceType::tableName() . '.reference_name speciality_name',
                StoredDirectionReferenceType::tableName() . '.reference_id speciality_code',
                StoredAdmissionCampaignReferenceType::tableName() . '.reference_id campaign_code',
                'user_v1.from1C',
                'bachelor_application.status AS abit_status',
                'bachelor_application.draft_status AS abit_draft_status',
                'COUNT(*) AS applications_count'
            ])
            ->from('bachelor_application')
            ->leftJoin('bachelor_speciality', 'bachelor_application.id = bachelor_speciality.application_id')
            ->leftJoin('dictionary_speciality', 'bachelor_speciality.speciality_id = dictionary_speciality.id')
            ->leftJoin(StoredDirectionReferenceType::tableName(), 'dictionary_speciality.direction_ref_id =' . StoredDirectionReferenceType::tableName() . '.id')
            ->leftJoin(StoredAdmissionCampaignReferenceType::tableName(), 'dictionary_speciality.campaign_ref_id =' . StoredAdmissionCampaignReferenceType::tableName() . '.id')
            ->leftJoin(
                ['user_v1' => $userQuery],
                'bachelor_application.user_id = user_v1.id'
            )
            ->leftJoin(
                ['draftless_bachelor_application' => $subBachelorApplicationQuery],
                'bachelor_application.user_id = draftless_bachelor_application.user_id'
            )
            ->andWhere(['bachelor_application.archive' => false])
            ->andWhere(['dictionary_speciality.archive' => false])
            ->andWhere('bachelor_application.draft_status = draftless_bachelor_application.max_draft_status')
            ->andWhere([
                'IN', 'bachelor_application.status',
                [
                    BachelorApplication::STATUS_SENT,
                    BachelorApplication::STATUS_CREATED,
                    BachelorApplication::STATUS_APPROVED,
                    BachelorApplication::STATUS_NOT_APPROVED,
                    BachelorApplication::STATUS_SENT_AFTER_APPROVED,
                    BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
                    BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
                ]
            ])
            ->andWhere([
                'NOT IN', 'bachelor_application.draft_status',
                [BachelorApplication::DRAFT_STATUS_MODERATING,]
            ])
            ->groupBy([
                StoredDirectionReferenceType::tableName() . '.reference_name',
                StoredDirectionReferenceType::tableName() . '.reference_id',
                StoredAdmissionCampaignReferenceType::tableName() . '.reference_id',
                'user_v1.from1C',
                'abit_status',
                'abit_draft_status',
            ]);

        if ($type == 'make-report') {
            $specList = $query->all();
            if (empty($specList)) {
                return $this->goBack();
            }

            $headerRow = array_keys(ArrayHelper::getValue($specList, '0') ?? []);
            $headerRow = array_map(
                function ($attr) use ($label) {
                    return $label[$attr] ?? '-';
                },
                $headerRow
            );
            $writer = new XLSXWriter();
            $writer->setAuthor('Some Author');
            $writer->writeSheetRow('Sheet1', $headerRow);
            foreach ($specList as $row) {
                $writer->writeSheetRow('Sheet1', ReportsPreprocessor::preprocessRow($row));
            }

            $data = date('d.m.Y');
            return Yii::$app->response->sendContentAsFile($writer->writeToString(), "Отчёт по специальностям от {$data}.xlsx");
        } else {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 100]
            ]);

            return $this->render(
                'spec_report',
                [
                    'label' => $label,
                    'dataProvider' => $dataProvider,
                ]
            );
        }
    }

    public function actionIndex()
    {
        $tnUser = User::tableName();
        $tnManageAC = ManageAC::tableName();
        $tnRBACAuthAssignment = RBACAuthAssignment::tableName();

        $dataProvider = new ActiveDataProvider([
            'query' => (new Query())
                ->from($tnRBACAuthAssignment)
                ->distinct()
                ->select([
                    "{$tnRBACAuthAssignment}.user_id",
                    "{$tnUser}.username",
                    "{$tnUser}.id"
                ])
                ->leftJoin(
                    $tnManageAC,
                    "{$tnRBACAuthAssignment}.user_id = {$tnManageAC}.rbac_auth_assignment_user_id"
                )
                ->leftJoin(
                    $tnUser,
                    "{$tnUser}.id = {$tnRBACAuthAssignment}.user_id"
                )
                ->where(["{$tnRBACAuthAssignment}.item_name" => RBACAuthItem::MANAGER])
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

        $application_type = ApplicationType::find()
            ->active()
            ->all();

        $manage_ac = ManageAC::find()
            ->select(['application_type_id'])
            ->where(['rbac_auth_assignment_user_id' => $id])
            ->asArray()
            ->all();

        $array_manger_ac = [];
        foreach ($manage_ac as $m) {
            $array_manger_ac[] = intval($m['application_type_id']);
        }

        return $this->render(
            'view',
            [
                'user' => $user,
                'application_type' => $application_type,
                'array_manger_ac' => $array_manger_ac,
            ]
        );
    }

    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        $manager_notifications_configurator = ManagerNotificationsConfigurator::getInstance($user);
        if (Yii::$app->request->isPost) {
            ManagerAllowChat::loadFromPost($id);

            $formName = (new ManageAC())->formName();
            $data = Yii::$app->request->post($formName);

            $manageAc = ManageAC::find()
                ->select(['application_type_id'])
                ->where(['rbac_auth_assignment_user_id' => $id])
                ->all();

            $manageAcInDb = [];
            foreach ($manageAc as $m) {
                $manageAcInDb[] = $m->application_type_id;
            }

            $manageAcPost = [];
            foreach ($data as $m) {
                $postAtId = intval($m['application_type_id']);
                if (!empty($postAtId)) {
                    $manageAcPost[] = $postAtId;
                }
            }

            $onDelete = array_diff($manageAcInDb, $manageAcPost);
            $onSave = array_diff($manageAcPost, $manageAcInDb);

            foreach ($onDelete as $idDelete) {
                $ms = ManageAC::findAll([
                    'application_type_id' => $idDelete,
                    'rbac_auth_assignment_user_id' => $id
                ]);
                foreach ($ms as $m) {
                    $m->delete();
                }
            }

            foreach ($onSave as $atId) {
                $m = new ManageAC();
                $m->rbac_auth_assignment_user_id = $id;
                $m->application_type_id = $atId;
                $m->save();
            }
            $manager_notifications_configurator->load(Yii::$app->request->post()) && $manager_notifications_configurator->save();

            return $this->redirect('index');
        }

        $applicationType = ApplicationType::find()
            ->active()
            ->all();

        $manageAc = ManageAC::find()
            ->select(['application_type_id'])
            ->where(['rbac_auth_assignment_user_id' => $id])
            ->asArray()
            ->all();

        $arrayManageAc = [];
        foreach ($manageAc as $m) {
            $arrayManageAc[] = intval($m['application_type_id']);
        }

        $managerAllowChat = ManagerAllowChat::getOrCreate($id);
        if (!$managerAllowChat->nickname) {
            $managerAllowChat->nickname = ManagerAllowChat::generateTemporaryNick($user);
        }

        return $this->render(
            'update',
            [
                'user' => $user,
                'arrayManageAc' => $arrayManageAc,
                'applicationType' => $applicationType,
                'managerAllowChat' => $managerAllowChat,
                'manager_notifications_configurator' => $manager_notifications_configurator,
            ]
        );
    }
}
