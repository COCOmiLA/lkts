<?php








namespace backend\controllers;

use backend\models\PodiumRoleRule;
use common\models\User;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ForumController extends Controller
{
    public function actionIndex()
    {
        $modelFields = [];
        $forumModel = null;
        $roleForModel = [];
        $modelIsEmpty = true;
        PodiumRoleRule::updateRole();
        $podiumRolesRules = PodiumRoleRule::find()->all();
        if (!empty($podiumRolesRules)) {
            $modelIsEmpty = false;
            $roleForModel = ArrayHelper::map($podiumRolesRules, 'role', 'rule');
            $modelFields = array_keys($roleForModel);
            $roleForModelLabels = ArrayHelper::map(
                $podiumRolesRules,
                'role',
                function ($podium) {
                    
                    return User::getRoleTranslatedName($podium->role);
                }
            );
            $forumModel = DynamicModel::validateData($roleForModel);
            $forumModel->addRule($modelFields, 'boolean');
            $forumModel->setAttributeLabels($roleForModelLabels);

            if (Yii::$app->request->isPost && $forumModel->load(Yii::$app->request->post())) {
                PodiumRoleRule::setRoleRule($podiumRolesRules, $forumModel);
            }
        }

        return $this->render(
            'index',
            [
                'forumModel' => $forumModel,
                'modelFields' => $modelFields,
                'modelIsEmpty' => $modelIsEmpty,
                'installed' => Yii::$app->getModule('student')->forumInLoader->forumIsInstalled(),
            ]
        );
    }
}
