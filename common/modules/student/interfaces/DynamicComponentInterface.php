<?php

namespace common\modules\student\interfaces;

interface DynamicComponentInterface{  
    public static function getConfig();
    public static function getController();
    public static function getUrlRules();
}