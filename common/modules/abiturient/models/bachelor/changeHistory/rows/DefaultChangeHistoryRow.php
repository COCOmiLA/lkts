<?php


namespace common\modules\abiturient\models\bachelor\changeHistory\rows;


use common\components\changeHistoryHandler\valueGetterHandler\DefaultChangeHistoryValueGetterHandler;
use common\components\changeHistoryHandler\valueGetterHandler\FromArrayChangeHistoryValueGetterHandler;
use common\models\EntrantManager;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\rows\interfaces\IChangeHistoryRow;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class DefaultChangeHistoryRow implements IChangeHistoryRow
{
    
    public $changeHistory;

    public function __construct(ChangeHistory $history)
    {
        $this->changeHistory = $history;
    }

    public function getChangeHistory(): ChangeHistory
    {
        return $this->changeHistory;
    }

    public function setChangeHistory(ChangeHistory $change)
    {
        $this->changeHistory = $change;
    }

    public function getRowTitle(): string
    {
        $title = '';
        if ($this->changeHistory->questionary_id !== null) {
            $titleText = Yii::t(
                'abiturient/change-history-widget',
                'Подпись группы "Анкета" в которой произошли изменения виджета истории изменений: `Анкета`'
            );
        } else {
            $titleText = Yii::t(
                'abiturient/change-history-widget',
                'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
            );
        }
        $title .= "<strong>{$titleText}.</strong> ";

        $description = ChangeHistoryClasses::getClassDescriptionByID($this->getChangeHistoryEntityClass()->entity_classifier_id);
        $title .= $description . '. ';

        $identifier = $this->getChangeHistoryEntityClass()->entity_identifier;

        if (!empty($identifier)) {
            $title .= $identifier . '.';
        }

        return $title;
    }

    public function getIcon(): string
    {
        if ($this->getChangeHistoryEntityClass() === null) {
            return '';
        }
        if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_INSERT) {
            return 'fa fa-plus';
        } else if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_UPDATE) {
            return 'fa fa-pencil';
        } else if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_DELETE) {
            return 'fa fa-trash';
        } else {
            return '';
        }
    }

    public function getIconColor(): string
    {
        if ($this->getChangeHistoryEntityClass() === null) {
            return '';
        }
        if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_INSERT) {
            return 'green';
        } else if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_UPDATE) {
            return 'blue';
        } else if ($this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_DELETE) {
            return 'red';
        } else {
            return 'blue';
        }
    }

    protected function getChangeHistoryEntityClass(): ?ChangeHistoryEntityClass
    {
        return $this->changeHistory->changeHistoryEntityClasses[0] ?? null;
    }

    protected function getValueGetter(ChangeHistoryEntityClass $class): ?DefaultChangeHistoryValueGetterHandler
    {
        $attrs = $class->changeHistoryEntityClassInputs ?? [];
        $attrs = ArrayHelper::map($attrs, 'input_name', 'value');
        return new FromArrayChangeHistoryValueGetterHandler($attrs);
    }

    protected function getOldValueGetter(ChangeHistoryEntityClass $class): ?DefaultChangeHistoryValueGetterHandler
    {
        $attrs = $class->changeHistoryEntityClassInputs ?? [];
        $attrs = ArrayHelper::map($attrs, 'input_name', 'old_value');
        return new FromArrayChangeHistoryValueGetterHandler($attrs);
    }

    public function getContent(): array
    {
        if ($this->getChangeHistoryEntityClass() === null) {
            return [];
        }
        $body = [];
        foreach ($this->getChangeHistory()->changeHistoryEntityClasses as $class) {
            $getter = $this->getValueGetter($class);
            $oldGetter = $this->getOldValueGetter($class);
            if ($class->entity_identifier !== null) {
                $body[] = [
                    'view' => '@abiturient/views/partial/changeHistoryModal/inputs/_identifierInput',
                    'data' => [
                        'identifier' => $class->entity_identifier
                    ]
                ];
            }
            foreach ($class->changeHistoryEntityClassInputs ?? [] as $input) {
                $value = $getter->getValue($input->input_name);
                $body[] = [
                    'view' => $input->getInputView(),
                    'data' => [
                        'value' => $this->prettifyValue($input->input_name, $value),
                        'old_value' => $this->prettifyValue($input->input_name, $oldGetter->getValue($input->input_name)),
                        'input_name' => $this->getAttributeName($class, $input->input_name, $value)
                    ]
                ];
            }
        }
        return $body;
    }

    protected function getAttributeName(ChangeHistoryEntityClass $class, string $attribute, $value): string
    {
        $class_name = ChangeHistoryClasses::getClassByID($class->entity_classifier_id);
        $entityClass = new $class_name();
        return $entityClass->attributeLabels()[$attribute] ?? $attribute;
    }

    protected function prettifyValue(string $attribute, $value)
    {
        return $value;
    }

    public function getInitiator(): string
    {
        $initiator = $this->getChangeHistory()->initiator;
        if ($initiator === null) {

            $initiator = $this->getChangeHistory()->entrantManager;
            if ($initiator === null) {
                throw new UserException('Для истории не задан инициатор.');
            }
        }
        $currentUser = \Yii::$app->user->identity;

        if ($initiator instanceof User) {
            if ($initiator->id === $currentUser->id) {
                return Yii::t(
                    'abiturient/change-history-widget',
                    'Расшифровка роли "Вы", которая стала инициатором изменений в виджете истории изменений: `Вы`'
                );
            }

            if ($currentUser->isModer()) {
                if ($initiator->isInRole(User::ROLE_ABITURIENT)) {
                    return Yii::t(
                        'abiturient/change-history-widget',
                        'Расшифровка роли "Поступающий", которая стала инициатором изменений в виджете истории изменений: `Поступающий`'
                    );
                }
                if ($initiator->isModer()) {
                    return $initiator->username;
                }
            }

            if ($currentUser->isInRole(User::ROLE_ABITURIENT)) {
                if ($initiator->isModer()) {
                    return Yii::t(
                        'abiturient/change-history-widget',
                        'Расшифровка роли "Сотрудник приемной кампании", которая стала инициатором изменений в виджете истории изменений: `Сотрудник приемной кампании`'
                    );
                }
            }
            if ($initiator->isAdmin()) {
                return Yii::t(
                    'abiturient/change-history-widget',
                    'Расшифровка роли "Администратор системы", которая стала инициатором изменений в виджете истории изменений: `Администратор системы`'
                );
            }
        } else if ($initiator instanceof EntrantManager) {

            if ($currentUser->isModer()) {
                return $initiator->getManagerName();
            }

            if ($currentUser->isInRole(User::ROLE_ABITURIENT)) {
                return Yii::t(
                    'abiturient/change-history-widget',
                    'Расшифровка роли "Сотрудник приемной кампании", которая стала инициатором изменений в виджете истории изменений: `Сотрудник приемной кампании`'
                );
            }
        }

        return Yii::t(
            'abiturient/change-history-widget',
            'Расшифровка роли "Не известно", которая стала инициатором изменений в виджете истории изменений: `Не известно`'
        );
    }
}
