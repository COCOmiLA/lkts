<?php


namespace common\components;


use common\components\exceptions\ArchiveAdmissionCampaignHandlerException;
use common\modules\abiturient\models\bachelor\BachelorApplication;





class ArchiveAdmissionCampaignHandler
{
    private $application;

    public function __construct(BachelorApplication $application = null)
    {
        $this->application = $application;
    }

    public function handle(BachelorApplication $application = null) {
        $applicationToCheck = $this->application ?? $application;

        if($applicationToCheck->type->campaignArchive) {
            throw new ArchiveAdmissionCampaignHandlerException();
        }
    }
}