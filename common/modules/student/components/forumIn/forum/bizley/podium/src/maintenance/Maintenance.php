<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\maintenance;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use Exception;
use yii\di\Instance;
use yii\rbac\DbManager;







class Maintenance extends SchemaOperation
{
    


    public $authManager;

    







    public function countPercent($currentStep, $maxStep)
    {
        $percent = $maxStep ? round(100 * $currentStep / $maxStep) : 0;
        if ($percent > 100) {
            $percent = 100;
        }
        if ($percent == 100 && $currentStep != $maxStep) {
            $percent = 99;
        }
        if ($percent == 100) {
            $this->clearCache();
        }
        return $percent;
    }

    




    public static function check()
    {
        try {
            (new Post())->tableSchema;
        } catch (Exception $e) {
            
            
            return false;
        }
        return true;
    }

    


    public function init()
    {
        parent::init();
        $this->authManager = Instance::ensure($this->module->rbac, DbManager::class);
    }

    



    public function clearCache()
    {
        $this->module->podiumCache->flush();
    }

    



    public function getSteps()
    {
        throw new Exception('This method must be overriden in Installation and Update class!');
    }
}
