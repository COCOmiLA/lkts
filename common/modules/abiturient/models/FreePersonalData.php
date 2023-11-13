<?php

namespace common\modules\abiturient\models;





class FreePersonalData extends PersonalData
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->validation_extender = null;
    }
}
