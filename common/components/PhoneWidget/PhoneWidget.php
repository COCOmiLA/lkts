<?php

namespace common\components\PhoneWidget;

use common\modules\abiturient\models\PersonalData;
use kartik\form\ActiveForm;
use yii\base\Widget;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

class PhoneWidget extends Widget
{
    
    public $form;

    
    public $disabled;

    
    public $phoneField;

    
    public $citizenId;

    
    public $isReadonly;

    
    public $personalData;

    
    public $fieldConfig;

    
    public static $phoneNumberMask = '(+9{1,3}|8|+1-9{1,3})\(9{1,5}\)9{1,50}';

    public function run()
    {
        return $this->render(
            '_phoneForm',
            [
                'form' => $this->form,
                'disabled' => $this->disabled,
                'citizenId' => $this->citizenId,
                'isReadonly' => $this->isReadonly,
                'phoneField' => $this->phoneField,
                'fieldConfig' => $this->fieldConfig,
                'personalData' => $this->personalData,
                'phoneNumberMask' => static::$phoneNumberMask,
            ]
        );
    }

    








    public static function renderField(
        ?ActiveForm  $form,
        PersonalData $model,
        string       $formFieldName,
        array        $config = [],
        array        $widget = []
    ) {
        if ($form) {
            return PhoneWidget::renderFieldWithForm($form, $model, $formFieldName, $config, $widget);
        }

        return PhoneWidget::renderFieldWithoutForm($model, $formFieldName, $widget);
    }

    








    private static function renderFieldWithForm(
        ?ActiveForm  $form,
        PersonalData $model,
        string       $formFieldName,
        array        $config = [],
        array        $widget = []
    ): string {
        $formField = $form->field($model, $formFieldName, $config);
        if ($widget && isset($widget['class']) && isset($widget['config'])) {
            $formField = $formField->widget(
                $widget['class'],
                $widget['config']
            );
        }

        $label = $model->getAttributeLabel($formFieldName) . ':';
        return $formField->label($label);
    }

    






    private static function renderFieldWithoutForm(PersonalData $model, string $formFieldName, array $widget = []): string
    {
        if ($widget && isset($widget['class']) && isset($widget['config'])) {
            $widgetConfig = array_merge(
                [
                    'name' => $formFieldName,
                    'value' => PhoneWidget::getModelValueByFormFieldName($model, $formFieldName),
                ],
                $widget['config']
            );

            return $widget['class']::widget($widgetConfig);
        }

        return PhoneWidget::renderSimpleField($model, $formFieldName);
    }

    





    private static function getModelValueByFormFieldName(PersonalData $model, string $formFieldName): string
    {
        $value = '';
        
        
        $pattern = '/(\w+)((\[)(\d+)(\])){0,1}/';
        
        
        
        
        
        
        
        
        
        
        
        
        if (preg_match($pattern, $formFieldName, $matches)) {
            
            $indexSplitPhone = ArrayHelper::getValue($matches, 4, null);
            
            $modelAttribute = ArrayHelper::getValue($matches, 1, '');

            $value = $model->{$modelAttribute};
            if (is_array($value) && isset($indexSplitPhone)) {
                $value = $value[$indexSplitPhone];
            }
        }

        return $value;
    }

    





    private static function renderSimpleField(PersonalData $model, string $formFieldName): string
    {
        return Html::input(
            'text',
            $formFieldName,
            PhoneWidget::getModelValueByFormFieldName($model, $formFieldName),
            ['class' => 'form-control']
        );
    }
}
