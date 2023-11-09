<?php

namespace common\modules\student\interfaces;

interface RoutableComponentInterface{  
    public function getComponentName();
    public function getBaseRoute();
    public function isAllowedToRole($role);
}