<?php

namespace common\modules\abiturient\models\bachelor;


use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\traits\HtmlPropsEncoder;
use yii\behaviors\TimestampBehavior;






class ApplicationHistory extends \yii\db\ActiveRecord implements IHaveIgnoredOnCopyingAttributes
{
    use HtmlPropsEncoder;

    const TYPE_QUESTIONARY_CHANGED = 0;
    const TYPE_INDIVIDUAL_ACH_CHANGED = 1;
    const TYPE_EXAM_CHANGED = 2;
    const TYPE_EDUCATION_CHANGED = 3;
    const TYPE_SPECIALITY_CHANGED = 4;
    const TYPE_DORMITORY_CHANGED = 5;
    const TYPE_NOT_KLADR = 6;
    
    const TYPE_AGREEMENT_CHANGED = 7;
    const TYPE_PARENT_DATA_CHANGED = 8;

    public static function tableName()
    {
        return '{{%application_history}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    


    public function rules()
    {
        return [
            [['application_id', 'type'], 'integer'],
            [['application_id', 'type'], 'required'],
            [['type'], 'in', 'range' => [self::TYPE_QUESTIONARY_CHANGED, self::TYPE_INDIVIDUAL_ACH_CHANGED,
                self::TYPE_EXAM_CHANGED, self::TYPE_EDUCATION_CHANGED,
                self::TYPE_SPECIALITY_CHANGED, self::TYPE_DORMITORY_CHANGED, self::TYPE_NOT_KLADR, self::TYPE_AGREEMENT_CHANGED, self::TYPE_PARENT_DATA_CHANGED]],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'application_id' => 'Заявление',
            'type' => 'Тип',
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getTypeName()
    {
        $names = static::historyTypeNames();
        return $names[$this->type] ?? '';
    }

    



    public static function historyTypeNames(): array
    {
        return [
            static::TYPE_QUESTIONARY_CHANGED => 'Анкета',
            static::TYPE_INDIVIDUAL_ACH_CHANGED => 'Индивидуальные достижения',
            static::TYPE_EXAM_CHANGED => 'Экзамены',
            static::TYPE_EDUCATION_CHANGED => 'Образование',
            static::TYPE_SPECIALITY_CHANGED => 'Направления подготовки',
            static::TYPE_DORMITORY_CHANGED => 'Запись в общежитие',
            static::TYPE_NOT_KLADR => 'Не заполнено по КЛАДР',
            static::TYPE_AGREEMENT_CHANGED => 'Согласия на зачисление',
            static::TYPE_PARENT_DATA_CHANGED => 'Данные родителей или законных представителей',
        ];
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            'id',
            'updated_at'
        ];
    }
}
