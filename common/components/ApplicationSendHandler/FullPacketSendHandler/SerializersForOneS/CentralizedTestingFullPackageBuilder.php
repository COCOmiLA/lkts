<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\EntrantTestManager\CentralizedTestingManager;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EgeResult;
use stdClass;
use yii\helpers\ArrayHelper;

class CentralizedTestingFullPackageBuilder extends BaseApplicationPackageBuilder
{
    
    protected $entrantTest;

    





    public function __construct(?BachelorApplication $application, ?EgeResult $entrantTest)
    {
        parent::__construct($application);

        $this->entrantTest = $entrantTest;
    }

    


    public function build(): array
    {
        return CentralizedTestingManager::buildRecalculationFor1C($this->entrantTest, true);
    }

    



    public function update($rawDatas): bool
    {
        if (!is_array($rawDatas) || ArrayHelper::isAssociative($rawDatas)) {
            $rawDatas = [$rawDatas];
        }

        return CentralizedTestingManager::proceedCentralizedTestingFrom1C($this->entrantTest, $rawDatas);
    }
}
