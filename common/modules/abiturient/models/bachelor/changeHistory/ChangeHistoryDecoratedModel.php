<?php

namespace common\modules\abiturient\models\bachelor\changeHistory;


use common\components\changeHistoryHandler\ApplicationActiveRecordChangeHistoryHandler;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\changeHistoryHandler\QuestionaryActiveRecordChangeHistoryHandler;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use yii\base\UserException;
use yii\db\ActiveRecord;







class ChangeHistoryDecoratedModel extends ActiveRecord implements ChangeLoggedModelInterface
{
    


    protected $changeHistoryHandler;

    


    protected $_oldAttributes = [];


    









    public function __construct($config = [])
    {
        parent::__construct($config);
        if ($this->getChangeHistoryHandler() === null) {
            if ($this instanceof ApplicationConnectedInterface) {
                $this->setChangeHistoryHandler(new ApplicationActiveRecordChangeHistoryHandler($this));
            } elseif ($this instanceof QuestionaryConnectedInterface) {
                $this->setChangeHistoryHandler(new QuestionaryActiveRecordChangeHistoryHandler($this));
            } else {
                throw new UserException('Ошибка сохранения историй изменения. Ожидалась сущность исполняющая интерфейс ApplicationConnectedInterface или QuestionaryConnectedInterface');
            }
        }
    }

    public function afterFind()
    {
        $this->_oldAttributes = $this->attributes;
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        
        $this->getChangeHistoryHandler()->setEntityIdentifier($this->getEntityIdentifier());
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        
        $this->getChangeHistoryHandler()->setEntityIdentifier($this->getEntityIdentifier());
        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->changeHistoryHandler
                ->getInsertHistoryAction()
                ->proceed();
        } else {
            $this->changeHistoryHandler
                ->getUpdateHistoryAction()
                ->proceed();
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->changeHistoryHandler
            ->getDeleteHistoryAction()
            ->proceed();
    }

    


    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler): void
    {
        $this->changeHistoryHandler = $handler;
    }

    


    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        return $this->changeHistoryHandler;
    }

    public function getChangeLoggedAttributes()
    {
        return [];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_UNDEFINED;
    }

    public function getOldAttribute($name)
    {
        return $this->_oldAttributes[$name] ?? null;
    }

    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        $class = new static();
        $class->attributes = $this->_oldAttributes;
        return $class;
    }

    public function getEntityIdentifier(): ?string
    {
        return null;
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT;
    }
}