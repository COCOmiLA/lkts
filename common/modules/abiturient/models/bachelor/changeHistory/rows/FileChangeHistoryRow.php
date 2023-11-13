<?php


namespace common\modules\abiturient\models\bachelor\changeHistory\rows;


use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;

class FileChangeHistoryRow extends DefaultChangeHistoryRow
{
    public function getContent(): array
    {
        if ($this->getChangeHistoryEntityClass() === null) {
            return [];
        }
        $body = [];
        if ($this->getChangeHistory()->changeHistoryEntityClasses[0]->entity_identifier !== null) {
            $body[] = [
                'view' => '@abiturient/views/partial/changeHistoryModal/inputs/_identifierInput',
                'data' => [
                    'identifier' => $this->getChangeHistory()->changeHistoryEntityClasses[0]->entity_identifier
                ]
            ];
        }
        $attachments = [];
        foreach ($this->getChangeHistory()->changeHistoryEntityClasses as $class) {
            $class_name = ChangeHistoryClasses::getClassByID($class->entity_classifier_id);
            $enClass = new $class_name();

            $attrs = $class->changeHistoryEntityClassInputs ?? [];
            foreach ($attrs as $attr) {
                $inputName = $attr->input_name;
                $enClass->$inputName = $attr->value ?? $attr->old_value;
            }
            $enClass->id = $class->entity_id;
            $attachments[] = $enClass;
        }
        $body[] = [
            'view' => "@abiturient/views/partial/changeHistoryModal/inputs/_fileInput",
            'data' => [
                'key' => $class->id,
                'attachments' => $attachments,
            ],
        ];
        return $body;
    }
}