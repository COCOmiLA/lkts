<?php

namespace common\models\dictionary;

use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\parentData\ParentData;














class FamilyType extends ModelFrom1CByOData
{
    protected static $referenceClassName = 'Справочник.СоставСемьи';
    protected static $referenceIdColumn = 'ref_id';
    protected static $referenceUidColumn = 'uid';
    protected static $referenceNameColumn = 'name';

        


    public static function tableName()
    {
        return '{{%dictionary_family_type}}';
    }

    


    public function rules()
    {
        return [
            [['name', 'uid', 'ref_id'], 'required'],
            [['updated_at', 'created_at',], 'integer'],
            [['archive'], 'boolean'],
            [['name', 'uid', 'ref_id'], 'string', 'max' => 255],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'uid' => 'Uid',
            'ref_id' => 'Ref ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'archive' => 'Archive',
        ];
    }
    
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::class,
        ];
    }
    
    




    public function getParentData()
    {
        return $this->hasMany(ParentData::class, ['type_id' => 'id']);
    }
}
