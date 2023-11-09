<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\EmptyCheck;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IReferencesOData;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;






class AdmissionCategory extends ModelFrom1CByOData implements IReferencesOData, IRestorableReferenceDictionary, IArchiveQueryable, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.КатегорииПриема';

    public function init()
    {
        parent::init();
        if (EmptyCheck::isEmpty($this->priority)) {
            $this->priority = '';
        }
    }

    


    public static function tableName()
    {
        return '{{%dictionary_admission_categories}}';
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
            [['ref_key', 'data_version', 'code', 'description', 'priority'], 'required'],
            [['ref_key',], 'string', 'max' => 255],
            [['code', 'description',], 'string', 'max' => 1000],
            [['data_version', 'priority'], 'string', 'max' => 100],
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


    




    static function getByCode($code)
    {
        return AdmissionCategory::find()
            ->where(['code' => $code])
            ->andWhere(['archive' => false])
            ->limit(1)
            ->one();
    }


    public static function find()
    {
        return new DictionaryQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public static function updateLinks()
    {
        $all_items = AdmissionCategory::find()
            ->active()
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {
                BachelorSpeciality::updateAll([
                    'admission_category_id' => ArrayHelper::getValue($item, 'id')
                ], [
                    'bachelor_speciality.category_code' => $item->{AdmissionCategory::$codeColumnName},
                    'admission_category_id' => null
                ]);

                CampaignInfo::updateAll(['admission_category_id' => ArrayHelper::getValue($item, 'id')],
                    [
                        'campaign_info.archive' => false,
                        'campaign_info.category_code' => $item->{AdmissionCategory::$codeColumnName},
                        'admission_category_id' => null
                    ]
                );

                AdmissionProcedure::updateAll(['admission_category_id' => ArrayHelper::getValue($item, 'id')], [
                    'dictionary_admission_procedure.archive' => false,
                    'dictionary_admission_procedure.category_code' => $item->{AdmissionCategory::$codeColumnName},
                    'admission_category_id' => null
                ]);
            }
        }
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            AdmissionProcedure::class,
            'admission_category_id'))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler($this,
            BachelorSpeciality::class,
            'admission_category_id'))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler($this,
            CampaignInfo::class,
            'admission_category_id'))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }

    public function fillDictionary()
    {
    }

    public static function getReferenceClassToFill(): string
    {
        return static::getReferenceClassName();
    }
}
