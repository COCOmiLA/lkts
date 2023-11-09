<?php

namespace common\modules\student\components\graduateWork\models;

class Theme
{
    public $subjectRef;
    public $termRef;
    public $theme;
    public $typeOfTheControlRef;
    public $teacherRef;
    public $orderDate;
    public $orderNumber;
    public $startDate;
    public $orderRef;

    public static function withData($data) {
        $instance = new self();

        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }

        return $instance;
    }
}