<?php

namespace common\models;

use common\components\SoapLoggers\ArrayWaySoapLogger;
use common\components\SoapLoggers\XmlWaySoapLogger;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;













class DebuggingSoap extends ActiveRecord
{
    public static function getInstance(): DebuggingSoap
    {
        $record = DebuggingSoap::find()->limit(1)->one();
        if (!$record) {
            $record = new DebuggingSoap();
            $record->loadDefaultValues();
        }
        return $record;
    }

    public static function tableName()
    {
        return '{{%debuggingsoap}}';
    }

    public function rules()
    {
        return [
            [[
                'enable_api_debug',
                'debugging_enable',
                'xml_debugging_enable',
                'enable_dummy_soap_mode',
                'model_validation_debugging_enable',
                'enable_logging_for_dictionary_soap',
                'enable_logging_for_kladr_soap',
            ], 'boolean'],
        ];
    }

    public function getIsLoggingForDictionarySoapEnabled(): bool
    {
        return $this->enable_logging_for_dictionary_soap ?? false;
    }

    public function getIsLoggingForKladrSoapEnabled(): bool
    {
        return $this->enable_logging_for_kladr_soap ?? false;
    }

    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'debugging_enable' => 'Включить логирование SOAP (в виде массива)',
            'xml_debugging_enable' => 'Включить логирование SOAP (в виде XML)',
            'enable_dummy_soap_mode' => 'Включить локальное перенаправление SOAP запросов',
            'enable_api_debug' => 'Включить логирование запросов API',
            'model_validation_debugging_enable' => 'Включить логирование ошибок при валидации моделей',
            'enable_logging_for_dictionary_soap' => 'Включить логирование SOAP запросов при обновлении справочников (рекомендуется включать ТОЛЬКО на время анализа инцидентов)',
            'enable_logging_for_kladr_soap' => 'Включить логирование SOAP запросов при обновлении адресного классификатора (рекомендуется включать ТОЛЬКО на время анализа инцидентов)',
        ];
    }

    public function getEnabledLoggers(): array
    {
        $ret = [];
        if ($this->debugging_enable) {
            $ret[] = new ArrayWaySoapLogger();
        }
        if ($this->xml_debugging_enable) {
            $ret[] = new XmlWaySoapLogger();
        }
        return $ret;
    }
}