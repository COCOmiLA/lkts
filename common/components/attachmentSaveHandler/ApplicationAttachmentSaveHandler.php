<?php
namespace common\components\attachmentSaveHandler;


use common\models\Attachment;
use common\models\interfaces\FileToShowInterface;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;





class ApplicationAttachmentSaveHandler extends BaseAttachmentSaveHandler
{
    


    private $application;

    public function __construct(FileToShowInterface $entity, BachelorApplication $application)
    {
        parent::__construct($entity, $application->user);
        $this->setApplication($application);
    }
    


    public function setApplication(BachelorApplication $application): void
    {
        $this->application = $application;
    }

    


    public function getApplication(): BachelorApplication
    {
        return $this->application;
    }


    protected function prepareAttachment(Attachment $attachment): Attachment
    {
        $newAttachment = parent::prepareAttachment($attachment);
        $newAttachment->application_id = $this->getApplication()->id;
        return $newAttachment;
    }

    protected function prepareChange(): ChangeHistory
    {
        $change = parent::prepareChange();
        $change->application_id = $this->application->id;
        return $change;
    }
}