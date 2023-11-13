<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use Closure;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\traits\BaseApplicationPackageBuilderTrait;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\services\FullPackageFilesSyncer;

abstract class BaseApplicationPackageBuilder
{
    use BaseApplicationPackageBuilderTrait;

    const EMPTY_DATE = '0001-01-01T00:00:00';

    
    protected ?Closure $_specialitiesFiltrationCallback = null;

    
    protected $application;

    
    protected $files_syncer;

    public function __construct(?BachelorApplication $app)
    {
        $this->application = $app;
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

    




    public function setSpecialitiesFiltrationCallback(?Closure $callback): BaseApplicationPackageBuilder
    {
        $this->_specialitiesFiltrationCallback = $callback;
        return $this;
    }

    


    protected function getSpecialitiesFiltrationCallback(): ?Closure
    {
        return $this->_specialitiesFiltrationCallback;
    }
}
