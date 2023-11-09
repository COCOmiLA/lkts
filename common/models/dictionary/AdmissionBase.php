<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use yii\behaviors\TimestampBehavior;





class AdmissionBase extends ModelFrom1CByOData implements IArchiveQueryable, IRestorableReferenceDictionary, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.ОснованияПоступления';
    
    


    public static function tableName()
    {
        return '{{%dictionary_admission_base}}';
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
            [['ref_key', 'data_version', 'code', 'description'], 'required'],
            [['ref_key', 'parent_key'], 'string', 'max' => 255],
            [['code', 'description', 'short_name'], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
            ['archive', 'boolean'],
            [['ref_key', 'data_version'], 'unique', 'targetAttribute' => ['ref_key', 'data_version']]
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'short_name' => 'сокращенное наименование',
            'parent_key' => 'родительский ключ 1С',
        ];
    }
    
    public function getParent(){
        return $this->hasOne(AdmissionBase::class, ['ref_key' => 'parent_key']);
    }
    
    public function getChildren(){
        return $this->hasMany(AdmissionBase::class, ['parent_key' => 'ref_key']);
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public function restoreDictionary()
    {
    }

    public function fillDictionary()
    {
    }

    public static function getReferenceClassToFill(): string
    {
        return static::getReferenceClassName();
    }
}
