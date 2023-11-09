<?php


namespace common\models\attachment\attachmentCollection;


use common\components\attachmentSaveHandler\QuestionaryAttachmentSaveHandler;
use common\models\Attachment;
use common\models\AttachmentType;
use common\modules\abiturient\models\AbiturientQuestionary;

class QuestionaryAttachmentCollection extends BaseAttachmentCollection
{

    


    public $questionary;

    






    public function __construct(AttachmentType $attachmentType, AbiturientQuestionary $questionary, $attachments = null)
    {
        parent::__construct($attachmentType, $questionary->user, $attachments);
        $this->questionary = $questionary;
        $this->formName = (new Attachment())->formName();

        $this->setAttachmentSaveHandler(new QuestionaryAttachmentSaveHandler($this, $questionary));
    }
}