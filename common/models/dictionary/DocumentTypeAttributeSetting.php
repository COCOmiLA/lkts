<?php

namespace common\models\dictionary;





class DocumentTypeAttributeSetting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%document_type_attribute_setting}}';
    }

    public function rules()
    {
        return [
            [[
                'name',
            ], 'required'],
            ['name', 'string'],
            ['properties_setting_id', 'integer'],
            [['is_used', 'is_required'], 'boolean'],
        ];
    }

    public function getDocumentTypeSetting()
    {
        return $this->hasOne(DocumentTypePropertiesSetting::class, [
            'id' => 'properties_setting_id'
        ]);
    }
}