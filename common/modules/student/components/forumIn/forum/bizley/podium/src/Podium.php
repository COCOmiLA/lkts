<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src;

use backend\models\PodiumRoleRule;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\DbTarget;
use common\modules\student\components\forumIn\forum\bizley\podium\src\maintenance\Maintenance;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Activity;
use Yii;
use yii\base\Action;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\console\Application as ConsoleApplication;
use yii\db\Connection;
use yii\i18n\Formatter;
use yii\rbac\DbManager;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;
use yii\web\Response;
use yii\web\User;

































class Podium extends Module implements BootstrapInterface
{
    


    protected $_version = '0.7';

    


    public $adminId;

    







    public $allowedIPs = ['127.0.0.1', '::1'];

    







    public $userComponent = true;

    







    public $rbacComponent = true;

    








    public $formatterComponent = true;

    






    public $dbComponent = 'db';

    







    public $cacheComponent = false;

    




    public $userPasswordField = 'password_hash';

    



    public $defaultRoute = 'forum';

    



    public $secureIdentityCookie = false;

    











    public $accessChecker;

    







    public $denyCallback;


    




    public function init()
    {
        parent::init();
        $this->setAliases(['@podium' => '@common/modules/student/components/forumIn/forum/bizley/podium/src']);
        if (Yii::$app instanceof WebApplication) {
            $this->podiumComponent->registerComponents();
            $this->layout = 'main';
        } else {
            $this->podiumComponent->registerConsoleComponents();
        }
    }

    




    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            $this->addUrlManagerRules($app);
            $this->setPodiumLogTarget($app);
        } elseif ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'common\modules\student\components\forumIn\forum\bizley\podium\src\console';
        }
    }

    






    public function afterAction($action, $result)
    {
        $parentResult = parent::afterAction($action, $result);
        if (Yii::$app instanceof WebApplication && !in_array($action->id, ['import', 'run', 'update', 'level-up'])) {
            Activity::add();
        }
        return $parentResult;
    }

    






    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $availableRole = PodiumRoleRule::getAvailableRole();
            if (!empty($availableRole)) {
                $userId = Yii::$app->user->getId();
                if (isset($userId)) {
                    $user_roles = Yii::$app->authManager->getRolesByUser($userId);
                    foreach ($user_roles as $r) {
                        if (in_array($r->name, $availableRole)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    




    protected function addUrlManagerRules($app)
    {
        $app->urlManager->addRules([new GroupUrlRule([
            'prefix' => $this->id,
            'rules' => require(__DIR__ . '/url-rules.php'),
        ])], true);
    }

    




    protected function setPodiumLogTarget($app)
    {
        $dbTarget = new DbTarget();
        $dbTarget->logTable = '{{%podium_log}}';
        $dbTarget->categories = ['common\modules\student\components\forumIn\forum\bizley\podium\src\*'];
        $dbTarget->logVars = [];
        $app->log->targets['podium'] = $dbTarget;
    }

    private $_cache;

    




    public function getPodiumCache()
    {
        if ($this->_cache === null) {
            $this->_cache = new PodiumCache();
        }
        return $this->_cache;
    }

    private $_config;

    




    public function getPodiumConfig()
    {
        if ($this->_config === null) {
            $this->_config = new PodiumConfig();
        }
        return $this->_config;
    }

    



    public function getInstalled()
    {
        return Maintenance::check();
    }

    



    public function getVersion()
    {
        return $this->_version;
    }

    





    public function prepareRoute($route)
    {
        return '/' . $this->id . (substr($route, 0, 1) === '/' ? '' : '/') . $route;
    }

    



    public function goPodium()
    {
        return Yii::$app->response->redirect([$this->prepareRoute('forum/index')]);
    }

    




    public function getLoginUrl()
    {
        if ($this->userComponent !== true) {
            return null;
        }
        return [$this->prepareRoute('account/login')];
    }

    




    public function getRegisterUrl()
    {
        if ($this->userComponent !== true) {
            return null;
        }
        return [$this->prepareRoute('account/register')];
    }

    private $_component;

    




    public function getPodiumComponent()
    {
        if ($this->_component === null) {
            $this->_component = new PodiumComponent($this);
        }
        return $this->_component;
    }

    





    public function getRbac()
    {
        return $this->podiumComponent->getComponent('rbac');
    }

    





    public function getFormatter()
    {
        return $this->podiumComponent->getComponent('formatter');
    }

    





    public function getUser()
    {
        return $this->podiumComponent->getComponent('user');
    }

    





    public function getDb()
    {
        return $this->podiumComponent->getComponent('db');
    }

    





    public function getCache()
    {
        return $this->podiumComponent->getComponent('cache');
    }
}
