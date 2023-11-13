<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\EntrantTestManager\ExamsScheduleManager;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EgeResult;
use stdClass;
use yii\helpers\ArrayHelper;

class ExamsScheduleFullPackageBuilder extends BaseApplicationPackageBuilder
{
    
    protected $entrantTest;

    





    public function __construct(?BachelorApplication $application, ?EgeResult $entrantTest)
    {
        parent::__construct($application);

        $this->entrantTest = $entrantTest;
    }

    


    public function build(): array
    {
        return ExamsScheduleManager::buildStructureTo1C($this->entrantTest);
    }

    



    public function update($rawDatas): bool
    {
        if (!is_array($rawDatas) || ArrayHelper::isAssociative($rawDatas)) {
            $rawDatas = [$rawDatas];
        }

        return ExamsScheduleManager::updateStructureTo1C($this->entrantTest, $rawDatas);
    }
}
