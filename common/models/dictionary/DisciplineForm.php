<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\interfaces\IReferencesOData;
use common\models\ModelLinkedToReferenceType;
use common\modules\abiturient\models\bachelor\EgeResult;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;










class DisciplineForm extends ModelLinkedToReferenceType implements IReferencesOData
{
    protected static $refKeyColumnName = 'discipline_form_ref_id';
    protected static $refClass = StoredDisciplineFormReferenceType::class;
    protected static $codeColumnName = 'discipline_form_id';

    


    public static function tableName()
    {
        return '{{%dictionary_discipline_form}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['discipline_form_id', 'discipline_form_name'], 'required'],
            [['discipline_form_id', 'discipline_form_name'], 'string', 'max' => 1000],
            [['discipline_form_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredDisciplineFormReferenceType::class, 'targetAttribute' => ['discipline_form_ref_id' => 'id']],
            ['archive', 'boolean']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'discipline_form_id' => 'Id form',
            'discipline_form_name' => 'Наименование формы',
        ];
    }

    public function getDisciplineFromRef()
    {
        return $this->hasOne(StoredDisciplineFormReferenceType::class, ['id' => 'discipline_form_ref_id']);
    }

    public static function updateLinks()
    {
        $all_items = DisciplineForm::find()
            ->where(['archive' => false])
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {

                EgeResult::updateAll(['exam_form_id' => ArrayHelper::getValue($item, 'id')], [
                    'bachelor_egeresult.exam_form' => $item->{DisciplineForm::$codeColumnName},
                    'exam_form_id' => null
                ]);
            }
        }
    }
}
