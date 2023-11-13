<?php


namespace common\components\AfterValidateHandler;

use common\components\AfterValidateHandler\interfaces\IAfterValidateHandler;






class LoggingAfterValidateHandler extends BaseAfterValidateHandler
{
    



    public function invoke(): IAfterValidateHandler
    {
        if(!$this->isModelValid()) {
            $value = \Yii::$app->configurationManager->getOrCreateDebuggingSoapModel()->model_validation_debugging_enable;
            if($value) {
                $modelClass = get_class($this->getModel());
                $text = "Произошли ошибки в время валидации модели {$modelClass}\n\nДанные модели:\n" . print_r($this->getModel()->attributes, true) ."\n\nОшибки:\n" . print_r($this->getModel()->errors, true);
                \Yii::error($text, "MODEL_DID_NOT_PASS_VALIDATION({$modelClass})");
            }
        }
        return $this;
    }
}