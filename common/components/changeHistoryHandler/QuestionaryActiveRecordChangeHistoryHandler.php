<?php


namespace common\components\changeHistoryHandler;


use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\interfaces\OwnerConnectedInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use yii\base\UserException;

class QuestionaryActiveRecordChangeHistoryHandler extends BaseChangeHistoryModelHandler
{
    



    public function persistChange(): ChangeHistory
    {
       $newChange = parent::persistChange();

       if(!$this->entity instanceof QuestionaryConnectedInterface) {
           throw new UserException('Ошибка сохранения истории изменений. Ожидался класс исполняющий интерфейс "QuestionaryConnectedInterface"');
       }

       $questionary = $this->getQuestionary();

       $newChange->questionary_id = $questionary->id;

       return $newChange;
    }

    public function getQuestionary(): AbiturientQuestionary {
        $questionry = $this->entity->abiturientQuestionary;
        if (is_null($questionry) && $this->entity instanceof OwnerConnectedInterface) {
            $questionry = $this->entity->owner->abiturientQuestionary;
        }

        if(is_null($questionry)) {
            throw new UserException("Невозможно записать историю изменений для прикрепляемого файла с ID {$this->entity->id}. Невозможно получить связанную с файлом анкету пользователя.");
        }

        return $questionry;
    }

}