<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\maintenance;

use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use Yii;










class Update extends Maintenance
{
    const SESSION_KEY = 'podium-update';
    const SESSION_STEPS = 'steps';
    const SESSION_VERSION = 'version';

    


    private $_steps;

    


    private $_versionSteps;

    




    public function nextStep()
    {
        $currentStep = Yii::$app->session->get(self::SESSION_KEY, 0);
        if ($currentStep === 0) {
            Yii::$app->session->set(self::SESSION_STEPS, count($this->versionSteps));
        }
        $maxStep = Yii::$app->session->get(self::SESSION_STEPS, 0);

        $this->table = '...';
        $this->type = self::TYPE_ERROR;

        if ($currentStep >= $maxStep) {
            return [
                'type' => $this->type,
                'result' => Yii::t('podium/flash', 'Weird... Update should already complete...'),
                'percent' => 100
            ];
        }

        if (!isset($this->versionSteps[$currentStep])) {
            return [
                'type' => $this->type,
                'result' => Yii::t('podium/flash', 'Update aborted! Can not find the requested update step.'),
                'percent' => 100,
            ];
        }

        $this->table = $this->versionSteps[$currentStep]['table'];
        $result = call_user_func_array([$this, $this->versionSteps[$currentStep]['call']], $this->versionSteps[$currentStep]['data']);

        Yii::$app->session->set(self::SESSION_KEY, ++$currentStep);
        return [
            'type' => $this->type,
            'result' => $result,
            'table' => $this->rawTable,
            'percent' => $this->countPercent($currentStep, $maxStep),
        ];
    }

    




    public function getVersionSteps()
    {
        if ($this->_versionSteps === null) {
            $currentVersion = Yii::$app->session->get(self::SESSION_VERSION, 0);
            $versionSteps = [];
            foreach ($this->steps as $version => $steps) {
                if (Helper::compareVersions(explode('.', $currentVersion), explode('.', $version)) == '<') {
                    $versionSteps += $steps;
                }
            }
            $this->_versionSteps = $versionSteps;
        }
        return $this->_versionSteps;
    }

    






    protected function updateValue($name, $value)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if ($value === null) {
            return Yii::t('podium/flash', 'Installation aborted! Column value missing.');
        }
        try {
            Podium::getInstance()->podiumConfig->set($name, $value);
            return $this->returnSuccess(Yii::t('podium/flash', 'Config setting {name} has been updated to {value}.', [
                'name'  => $name,
                'value' => $value,
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during configuration updating')
            );
        }
    }

    



    public function getSteps()
    {
        if ($this->_steps === null) {
            $this->_steps = require(__DIR__ . '/steps/update.php');
        }
        return $this->_steps;
    }
}
