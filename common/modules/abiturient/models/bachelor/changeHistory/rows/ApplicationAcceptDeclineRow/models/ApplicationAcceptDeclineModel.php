<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models;


use common\components\changeHistoryHandler\ApplicationActiveRecordChangeHistoryHandler;
use common\components\changeHistoryHandler\decorators\ApplicationAcceptDeclineChangeHistoryDecorator;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use yii\base\Model;





class ApplicationAcceptDeclineModel extends Model implements ChangeLoggedModelInterface, ApplicationConnectedInterface
{

    public const APPLICATION_ACCEPTED = 1;
    public const APPLICATION_REJECT = 0;

    private $_changeHistoryHandler;

    


    public $application;
    


    public $application_action_status;

    


    public $application_comment;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->setChangeHistoryHandler(new ApplicationAcceptDeclineChangeHistoryDecorator(new ApplicationActiveRecordChangeHistoryHandler($this)));
    }

    public function rules()
    {
        return [
            [['application_action_status'], 'int'],
            ['application_comment', 'string'],
            ['application', 'safe'],
        ];
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function attributeLabels()
    {
        return [
            'application_comment' => 'Комментарий'
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_APPLICATION_ACCEPT_REJECT;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'application_comment',
        ];
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        return null;
    }

    


    public function getChangeHistoryHandler(): ChangeHistoryHandlerInterface
    {
        return $this->_changeHistoryHandler;
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT;
    }

    


    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler): void
    {
        $this->_changeHistoryHandler = $handler;
    }

    public function getPrimaryKey($asArray = false)
    {
        return $this->application->id;
    }

    public function getOldAttribute($name)
    {
        return null;
    }

    public function getOldAttributes()
    {
        return null;
    }
}