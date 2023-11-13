<?php

namespace common\modules\student\models;

class Error
{
    public $code;
    public $description;
    
    public static function fromRaw($data): Error
    {
        $instance = new Error();
        
        $instance->code = $data->Code ?? null;
        $instance->description = $data->Description ?? null;
        
        return $instance;
    }
}
