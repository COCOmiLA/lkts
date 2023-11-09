<?php

namespace common\models\dictionary;



class KladrCode extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%kladr_codes}}';
    }

    


    public function rules()
    {
        return [
            [['code'], 'string', 'max' => 255],
            [['code'], 'required'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'code' => 'Код',
        ];
    }

    public function getFiasHouse()
    {
        return $this->hasOne(FiasDoma::class, ['code_id' => 'id']);
    }

    public static function getIdOrCreate(string $code): int
    {
        $model_id = KladrCode::find()->where(['code' => $code])->select('id')->limit(1)->scalar();
        if (!$model_id) {
            $model = new KladrCode();
            $model->code = $code;
            $model->save(false);
            $model_id = $model->id;
        }
        return $model_id;
    }
}