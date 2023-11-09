<?php

namespace common\modules\student\components\block\models;

use common\modules\student\models\Error;
use common\modules\student\models\ResultType;

class CreateIndividualResult
{
    
    public $result;

    
    public $error = [];

    public static function fromRaw($data): CreateIndividualResult
    {
        $instance = new CreateIndividualResult();

        $instance->result = $data->Result ?? ResultType::FAIL;

        if (isset($data->Error)) {
            $errors = is_array($data->Error) ? $data->Error : [$data->Error];
            foreach ($errors as $error) {
                $instance->error[] = Error::fromRaw($error);
            }
        }

        return $instance;
    }
}
