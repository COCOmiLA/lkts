<?php


namespace common\models\attachment\attachmentCollection;


use common\components\attachmentSaveHandler\ApplicationAttachmentSaveHandler;
use common\models\Attachment;
use common\models\AttachmentType;
use common\models\interfaces\AttachmentInterface;
use common\modules\abiturient\models\bachelor\BachelorApplication;

class ApplicationAttachmentCollection extends BaseAttachmentCollection
{

    


    public $application;

    





    public function __construct(AttachmentType $attachmentType, BachelorApplication $application, $attachments = null)
    {
        parent::__construct($attachmentType, $application->user, $attachments);
        $this->application = $application;
        $this->formName = (new Attachment())->formName();
        $this->setAttachmentSaveHandler(new ApplicationAttachmentSaveHandler($this, $application));
    }
}