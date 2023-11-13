<?php


namespace common\components\changeHistoryHandler;


use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use yii\base\UserException;
use yii\db\ActiveQuery;

class ApplicationActiveRecordChangeHistoryHandler extends BaseChangeHistoryModelHandler
{
    



    public function persistChange(): ChangeHistory
    {
       $newChange = parent::persistChange();

       if(!$this->entity instanceof ApplicationConnectedInterface) {
           throw new UserException('Ошибка сохранения истории изменений. Ожидался класс исполняющий интерфейс "ApplicationConnectedInterface"');
       }

       $probablyApplication = $this->entity->getApplication();
       $application = null;

       if($probablyApplication instanceof ActiveQuery) {
            $application = $probablyApplication->one();
       } elseif($probablyApplication instanceof BachelorApplication) {
           $application = $probablyApplication;
       } else {
           throw new UserException('Ошибка сохранения историй изменений. Результатом вызова getApplication ожидался класс BachelorApplication или ActiveQuery');
       }

       if($application !== null) {
           $newChange->application_id = $application->id;
       } else {
           throw new UserException('Ошибка сохранения истории изменений. Ожидался класс исполняющий интерфейс "ApplicationConnectedInterface"');
       }


       return $newChange;
    }

}