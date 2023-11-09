<?php
namespace common\models\validators;

use common\models\UserRegulation;
use yii\validators\Validator;
use yii\web\View;

class UserRegulationCheckboxValidator extends Validator {

    public function init() {
        parent::init ();
        $this->message = 'Необходимо подтвердить прочтение нормативного документа.';
    }

    



    public function validateAttribute($model , $attribute ) {

        if ($model->regulation->confirm_required && !$model->is_confirmed) {
            $model->addError ( $attribute , $this->message );
        }
    }

    





    public function clientValidateAttribute( $model , $attribute , $view ) {

        $message = json_encode ( $this->message , JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $script = '';
        if($model->regulation->confirm_required) {
            $script = <<<JS
            if ($("$attribute").val()=="") {
                messages.push($message);
            }
JS;
        }

        return $script;
    }
}