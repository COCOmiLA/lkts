<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;




class BudgetLevel extends ModelFrom1CByOData implements IArchiveQueryable, IRestorableReferenceDictionary, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.УровниБюджета';
    
    


    public static function tableName()
    {
        return '{{%dictionary_budget_level}}';
    }

    


    public function rules()
    {
        return [
            [['ref_key', 'data_version', 'code', 'description'], 'required'],
            [['ref_key', 'predefined_data_name'], 'string', 'max' => 255],
            [['predefined_data_name'], 'string', 'max' => 1000],
            [['code', 'description',], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
            [['has_deletion_mark', 'is_predefined'], 'boolean'],
            [['has_deletion_mark'], 'safe'],
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
            'priority' => 'приоритет',
        ];
    }


    




    static function getByCode($code){
        return BudgetLevel::find()
            ->where(['code' => $code])
            ->andWhere(['archive' => false])
            ->limit(1)
            ->one();
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
