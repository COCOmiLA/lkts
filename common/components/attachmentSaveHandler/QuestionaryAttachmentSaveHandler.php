<?php
namespace common\components\attachmentSaveHandler;


use common\models\Attachment;
use common\models\interfaces\FileToShowInterface;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;





class QuestionaryAttachmentSaveHandler extends BaseAttachmentSaveHandler
{
    


    private $questionary;

    public function __construct(FileToShowInterface $entity, AbiturientQuestionary $questionary)
    {
        parent::__construct($entity, $questionary->user);
        $this->setQuestionary($questionary);
    }

    


    public function getQuestionary(): AbiturientQuestionary
    {
        return $this->questionary;
    }

    


    public function setQuestionary(AbiturientQuestionary $questionary): void
    {
        $this->questionary = $questionary;
    }

    protected function prepareAttachment(Attachment $attachment): Attachment
    {
        $newAttachment = parent::prepareAttachment($attachment);
        $newAttachment->questionary_id = $this->getQuestionary()->id;
        return $newAttachment;
    }

    protected function prepareChange(): ChangeHistory
    {
        $change = parent::prepareChange();
        $change->questionary_id = $this->questionary->id;
        return $change;
    }
}