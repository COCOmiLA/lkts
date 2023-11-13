<?php

namespace common\modules\student\components\orders\models;








class Order {

    public $orderTitle;

    public function __construct($orderTitle) {
        $this->orderTitle = $orderTitle;
    }
}
