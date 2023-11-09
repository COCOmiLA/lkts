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
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\CgetConditionType;
use common\modules\abiturient\models\bachelor\CgetRequiredPreference;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;








class Privilege extends ModelFrom1CByOData implements IReferencesOData, IRestorableReferenceDictionary, IArchiveQueryable, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.Льготы';
    public const KEY = 'priv';

    public function init()
    {
        parent::init();
        if (EmptyCheck::isEmpty($this->full_name)) {
            $this->full_name = '';
        }
    }

    


    public static function tableName()
    {
        return '{{%dictionary_privileges}}';
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
            [['ref_key', 'code', 'description'], 'required'],
            [['ref_key', 'parent_key'], 'string', 'max' => 255],
            [['code', 'description', 'full_name'], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'full_name' => 'полное наименование',
            'parent_key' => 'родительский ключ 1С',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(Privilege::class, ['ref_key' => 'parent_key']);
    }

    public function getChildren()
    {
        return $this->hasMany(Privilege::class, ['parent_key' => 'ref_key']);
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
        $all_items = Privilege::find()
            ->where(['archive' => false])
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {
                BachelorPreferences::updateAll(['privilege_id' => ArrayHelper::getValue($item, 'id')], [
                    'bachelor_preferences.archive' => false,
                    'bachelor_preferences.privilege_code' => $item->{Privilege::$codeColumnName},
                    'privilege_id' => null
                ]);

                AdmissionProcedure::updateAll(['privilege_id' => ArrayHelper::getValue($item, 'id')], [
                    'dictionary_admission_procedure.archive' => false,
                    'dictionary_admission_procedure.privilege_code' => $item->{Privilege::$codeColumnName},
                    'privilege_id' => null
                ]);
            }
        }
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            BachelorPreferences::class,
            'privilege_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            AdmissionProcedure::class,
            'privilege_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CgetRequiredPreference::class,
            'dictionary_privileges_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CgetConditionType::class,
            'privilege_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }

    


    public function getAdmissionProcedures()
    {
        return $this->hasMany(AdmissionProcedure::class, ['privilege_id' => 'id']);
    }

    public function getHashCode()
    {
        
        $priority_rights = $this->getAdmissionProcedures()->select('priority_right')->distinct()->column();
        $priority_right_verdict = $priority_rights[0];
        if (count($priority_rights) > 1) { 
            $priority_right_verdict = 2;
        }

        return "{$this->ref_key}_" . Privilege::KEY . "_{$priority_right_verdict}";
    }

    public function fillDictionary()
    {
    }

    public static function getReferenceClassToFill(): string
    {
        return static::getReferenceClassName();
    }
}
