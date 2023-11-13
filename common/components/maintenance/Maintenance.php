<?php
namespace common\components\maintenance;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;





class Maintenance extends Component implements BootstrapInterface
{
    



    public $enabled;
    



    public $catchAllRoute;

    



    public $retryAfter = 300;
    


    public $maintenanceLayout = '@frontend/views/layouts/main.php';
    


    public $maintenanceView = '@common/components/maintenance/views/maintenance/index.php';
    


    public $maintenanceText;

    



    public function bootstrap($app)
    {
        if ($this->enabled instanceof \Closure) {
            $enabled = call_user_func($this->enabled, $app);
        } else {
            $enabled = $this->enabled;
        }
        if ($enabled) {
            $this->maintenanceText = $this->maintenanceText ?: Yii::t('common', 'Закрыто на обслуживание');
            if ($this->catchAllRoute === null) {
                $app->controllerMap['maintenance'] = [
                    'class' => 'common\components\maintenance\controllers\MaintenanceController',
                    'retryAfter' => $this->retryAfter,
                    'maintenanceLayout' => $this->maintenanceLayout,
                    'maintenanceView' => $this->maintenanceView,
                    'maintenanceText' => $this->maintenanceText
                ];
                $app->catchAll = ['maintenance/index'];
                Yii::$app->view->registerAssetBundle(MaintenanceAsset::class);
            } else {
                $app->catchAll = [
                    $this->catchAllRoute,
                    'retryAfter' => $this->retryAfter,
                    'maintenanceText' => $this->maintenanceText
                ];
            }
        }
    }
}
