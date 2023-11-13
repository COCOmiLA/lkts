<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;






class SpecialityGroupingMode extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%dictionary_speciality_grouping_modes}}';
    }

    public function rules()
    {
        return [
            [['description', 'code_name'], 'required'],
            [['description', 'code_name'], 'string', 'max' => 255],
        ];
    }

    public function getAdmissionCampaigns()
    {
        return $this->hasMany(AdmissionCampaign::class, ['id' => 'campaign_id'])
            ->viaTable('{{%admission_campaign_grouping_modes_junction}}', ['grouping_mode_id' => 'id']);
    }

    public static function GetOrCreateBy(string $code_name, string $description): SpecialityGroupingMode
    {
        $model = self::findOne(['code_name' => $code_name]);
        if ($model === null) {
            $model = new SpecialityGroupingMode();
            $model->code_name = $code_name;
        }
        if ($model->description !== $description) {
            $model->description = $description;
            if (!$model->save()) {
                throw new RecordNotValid($model);
            }
        }
        return $model;
    }
}