<?php

namespace common\modules\student;

use common\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\controllers\StudentController;
use common\modules\student\controllers\TeacherController;
use common\modules\student\interfaces\DynamicComponentInterface;
use common\modules\student\interfaces\RoutableComponentInterface;
use Throwable;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface
{
    public $controllerNamespace = 'common\modules\student\controllers';

    public function bootstrap($app)
    {
        Yii::$app->controllerMap[User::ROLE_STUDENT] = StudentController::class;
        Yii::$app->controllerMap[User::ROLE_TEACHER] = TeacherController::class;
        $app->getUrlManager()->addRules(
            ['student/forum' => 'forum/default/index'],
            false
        );
        $app->setModule('podium', [
            'class' => Podium::class,
            'userComponent' => User::ROLE_USER,
            'adminId' => 1,
        ]);
        $this->loadComponents();
    }

    protected function findLoader(string $path, string $namespace): ?string
    {
        try {
            $files = array_diff(scandir($path), array('..', '.'));
            foreach ($files as $file) {
                if (str_ends_with($file, 'Loader.php')) {
                    return substr("{$namespace}\\{$file}", 0, -4); 
                }
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function loadComponents()
    {
        $components = array_diff(scandir($this->getComponentsPath()), array('..', '.'));
        $components_to_set = [];
        $controllers_map = [];
        $url_rules = [];
        foreach ($components as $component) {
            if (str_ends_with($component, '.php')) {
                continue;
            }
            $loader_namespace = __NAMESPACE__ . '\\components\\' . $component;
            $component_loader = $this->findLoader($this->getComponentsPath() . DIRECTORY_SEPARATOR . $component, $loader_namespace);
            if (class_exists($component_loader)) {
                $component_name = "{$component}Loader";
                $interfaces = class_implements($component_loader);
                if (isset($interfaces[DynamicComponentInterface::class])) {
                    $loaded_components = $this->getComponents();
                    if (!isset($loaded_components[$component_name])) {
                        $config = $component_loader::getConfig();
                        $components_to_set[$component_name] = $config;
                        $controllers_map[$component] = $component_loader::getController();
                        $url_rules[$component_loader] = $component_loader::getUrlRules();
                    }
                }
            }
        }
        if ($components_to_set) {
            $this->setComponents($components_to_set);
            foreach ($controllers_map as $component => $map) {
                Yii::$app->controllerMap[strtolower($component)] = $map;
            }
            foreach ($url_rules as $rule) {
                Yii::$app->urlManager->addRules($rule);
            }
        }
    }

    public function getComponentsPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'components';
    }

    private function getRoleIfEmpty()
    {
        $user = Yii::$app->user->identity;
        if ($user->isInRole(User::ROLE_STUDENT)) {
            return User::ROLE_STUDENT;
        } elseif ($user->isInRole(User::ROLE_TEACHER)) {
            return User::ROLE_TEACHER;
        }

        return User::ROLE_USER;
    }

    public function getRoutes($role = null)
    {
        $components = $this->getComponents();
        $routes = [];
        if (empty($role)) {
            $role = Module::getRoleIfEmpty();
        }
        foreach ($components as $name => $component) {
            $interfaces = class_implements($component['class']);
            if (isset($interfaces[RoutableComponentInterface::class]) && $this->$name->isAllowedToRole($role)) {
                $routes[$this->$name->getBaseRoute()] = $this->$name->getComponentName();
            }
        }
        return $routes;
    }

    public function getComponentsFilteredByRole($role = null)
    {
        $components = $this->getComponents();
        $routes = [];
        if (empty($role)) {
            $role = Module::getRoleIfEmpty();
        }
        foreach ($components as $name => $component) {
            $interfaces = class_implements($component['class']);
            if (isset($interfaces[RoutableComponentInterface::class]) && $this->$name->isAllowedToRole($role)) {
                $routes[] = $component['class'];
            }
        }
        return $routes;
    }
}
