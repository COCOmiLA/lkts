<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\console;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\rbac\Rbac;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\rbac\Role;






class UserController extends Controller
{

    




    public function actionAssignRole($idOrEmail, $role)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('No such role.' . PHP_EOL);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if (strpos($role->name, 'podium') === 0) {
            $this->setPodiumUserRole($user, $role);
        } else {
            $rbac->assign($role, $user->id);
        }
        $this->stdout("user#{$user->id} has role '{$role->name}'" . PHP_EOL);
    }

    




    public function actionRevokeRole($idOrEmail, $role)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('No such role.' . PHP_EOL);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if (strpos($role->name, 'podium') === 0) {
            $defaultPodiumRole = $rbac->getRole(Rbac::ROLE_USER);
            $this->setPodiumUserRole($user, $defaultPodiumRole);
            $this->stdout("user#{$user->id} has role '{$defaultPodiumRole->name}'" . PHP_EOL);
        } else {
            $rbac->revoke($role, $user->id);
            $this->stdout("user#{$user->id} role '{$role->name}' revoked" . PHP_EOL);
        }
    }

    



    public function actionShowRoles($idOrEmail)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $roles = Podium::getInstance()->getRbac()->getRolesByUser($user->id);

    }

    




    protected function setPodiumUserRole($user, $role)
    {
        $rbac = Podium::getInstance()->getRbac();
        $userRoles = $rbac->getRolesByUser($user->id);
        $podiumRoles = array_filter($userRoles, function ($role) {
            return strpos($role->name, 'podium') === 0;
        });
        foreach ($podiumRoles as $podiumRole) {
            $rbac->revoke($podiumRole, $user->id);
        }
        $rbac->assign($role, $user->id);
    }

    




    protected function findUser($idOrEmail)
    {
        if (!$user = User::find()->andWhere(is_numeric($idOrEmail) ? ['id' => $idOrEmail] : ['email' => $idOrEmail])->limit(1)->one()) {
            $this->stderr('User not found.' . PHP_EOL);
        }
        return $user;
    }
}