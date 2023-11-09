<?php


namespace common\models;

use yii\helpers\Html;









class DummySoapResponse extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%dummy_soap_response}}';
    }

    public function getMethodFormattedResponse()
    {
        $str = Html::encode($this->method_response);
        return "<pre>{$str}</pre>";
    }

    public function rules()
    {
        return [
            [['method_name', 'method_response'], 'trim'],
            [['method_response'], 'filter', 'filter' => function ($value) {
                return str_replace('m:', '', $value);
            }],
            [['method_name', 'method_response'], 'required'],
            [['method_response'], function ($attribute, $params) {
                try {
                    $_ = simplexml_load_string($this->$attribute);
                } catch (\Throwable $e) {
                    $this->addError($attribute, 'Введённый xml не корректен');
                }
            }],

            [['method_name'], 'string', 'max' => 255],
            [['method_response'], 'string'],
        ];
    }

    public function saveMethod()
    {
        


        $existing = DummySoapResponse::find()->where(['method_name' => $this->method_name])->one();
        if (is_null($existing)) {
            $this->save();
        } else {
            $existing->method_response = $this->method_response;
            $existing->save();
        }
    }

    


    public function attributeLabels()
    {
        return [
            'method_name' => 'Название метода SOAP',
            'method_response' => 'Результат выполнения SOAP запроса',
            'methodFormattedResponse' => 'SOAP ответ',
        ];
    }
}