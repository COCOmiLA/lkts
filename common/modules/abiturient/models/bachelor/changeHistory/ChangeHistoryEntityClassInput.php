<?php

namespace common\modules\abiturient\models\bachelor\changeHistory;


use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\traits\HtmlPropsEncoder;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;














class ChangeHistoryEntityClassInput extends ActiveRecord implements IHaveIgnoredOnCopyingAttributes
{
    use HtmlPropsEncoder;

    


    public static function tableName()
    {
        return '{{%change_history_entity_class_input}}';
    }

    


    public function rules()
    {
        return [
            [['entity_class_id'], 'integer'],
            [['value', 'old_value', 'archived_value', 'archived_old_value'], 'string', 'max' => 2000],
            [['input_name'], 'string', 'max' => 400],
            [['entity_class_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChangeHistoryEntityClass::class, 'targetAttribute' => ['entity_class_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Value',
            'old_value' => 'Old Value',
            'input_name' => 'Input Name',
            'entity_class_id' => 'Entity Class ID',
        ];
    }

    




    public function getEntityClass()
    {
        return $this->hasOne(ChangeHistoryEntityClass::class, ['id' => 'entity_class_id']);
    }

    public function setEntityClass(ChangeHistoryEntityClass $class)
    {
        $this->entity_class_id = $class->id;
    }

    



    public function setValue($new_value): void
    {
        if (!empty($new_value) && is_array($new_value)) {
            $tmpValue = '«';
            $tmpValue .= implode('», «', $new_value);
            $tmpValue .= '»';

            $new_value = $tmpValue;
        }
        $this->value = empty($new_value) ? null : (string)$new_value;
    }

    



    public function setOldValue($old_value): void
    {
        $this->old_value = empty($old_value) ? null : (string)$old_value;
    }

    public function getInputView()
    {
        $base = "@abiturient/views/partial/changeHistoryModal/inputs/";

        if ($this->entityClass->entity_classifier_id === ChangeHistoryClasses::CLASS_ATTACHMENT || $this->entityClass->entity_classifier_id === ChangeHistoryClasses::CLASS_ATTACHMENT_COLLECTION) {
            return $base . '_fileInput';
        }

        return $base . '_defaultTextInput';
    }


    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            'id',
            'updated_at'
        ];
    }
}
