<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\traits\BaseApplicationPackageBuilderTrait;
use common\models\EmptyCheck;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\services\FullPackageFilesSyncer;

abstract class BaseQuestionaryPackageBuilder
{
    const EMPTY_DATE = '0001-01-01T00:00:00';

    use BaseApplicationPackageBuilderTrait;

    
    protected $questionary;

    
    protected $files_syncer;

    public function __construct(AbiturientQuestionary $questionary)
    {
        $this->questionary = $questionary;
    }

    public function build()
    {
        return '';
    }

    public function update($raw_data)
    {
        return true;
    }

    public function setFilesSyncer(?FullPackageFilesSyncer $filesSyncer)
    {
        $this->files_syncer = $filesSyncer;
        return $this;
    }
}