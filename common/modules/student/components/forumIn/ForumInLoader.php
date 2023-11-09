<?php







namespace common\modules\student\components\forumIn;


use common\models\User;
use Yii;

class ForumInLoader extends \yii\base\Component implements \common\modules\student\interfaces\DynamicComponentInterface,
    \common\modules\student\interfaces\RoutableComponentInterface
{
    public $componentName = "Форум";
    public $baseRoute = 'student/forumin';

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    public function isAllowedToRole($role)
    {
        if (!$this->forumIsInstalled()) {
            return false;
        }
        switch ($role) {
            case (User::ROLE_TEACHER):
            case(User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }

    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\forumIn\ForumInLoader',
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\ForumInController';
    }

    public static function getUrlRules()
    {
        return [
            'student/forumin' => 'forumin/index',
        ];
    }

    public function forumIsInstalled(): bool
    {
        return !!Yii::$app->db->getTableSchema('podium_user');
    }
}