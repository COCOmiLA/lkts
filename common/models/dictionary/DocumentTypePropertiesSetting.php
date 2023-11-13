<?php

namespace common\models\dictionary;

use common\models\errors\RecordNotValid;




class DocumentTypePropertiesSetting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%document_type_properties_settings}}';
    }

    public function rules()
    {
        return [
            ['document_type_id', 'required'],
            ['document_type_id', 'integer']
        ];
    }

    public function getAttributeSettings()
    {
        return $this->hasMany(DocumentTypeAttributeSetting::class, [
            'properties_setting_id' => 'id'
        ]);
    }

    




    public static function getPropertySetting(DocumentType $documentType, string $name): array
    {
        $setting = static::find()
            ->joinWith(['documentType document_type'])
            ->andWhere(['document_type.ref_key' => $documentType->ref_key])
            ->one();
        if (!$setting) {
            return [false, true];
        }
        $prop_setting = $setting->getAttributeSettings()->andWhere(['name' => $name])->one();
        if (!$prop_setting) {
            return [false, true];
        }
        return [(bool)$prop_setting->is_required && (bool)$prop_setting->is_used, (bool)$prop_setting->is_used];
    }

    public static function getOrCreateByDocumentType(DocumentType $documentType): DocumentTypePropertiesSetting
    {
        $result = static::find()->where(['document_type_id' => $documentType->id])->one();
        if (!$result) {
            $result = new DocumentTypePropertiesSetting();
            $result->document_type_id = $documentType->id;
            if (!$result->save()) {
                throw new RecordNotValid($result);
            }
        }
        return $result;
    }

    public function getDocumentType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }

    public function setupPropertySetting(string $name, bool $is_used, bool $is_required): DocumentTypeAttributeSetting
    {
        $setting = $this->getAttributeSettings()->andWhere(['name' => $name])->one();
        if (!$setting) {
            $setting = new DocumentTypeAttributeSetting();
            $setting->properties_setting_id = $this->id;
            $setting->name = $name;
        }
        $setting->is_used = $is_used;
        $setting->is_required = $is_required;
        $setting->save();
        return $setting;
    }
}