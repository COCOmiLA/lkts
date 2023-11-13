<?php

namespace common\models\traits;

trait ScenarioWithoutExistValidationTrait
{
    public static $SCENARIO_WITHOUT_EXISTS_CHECK = 'SCENARIO_WITHOUT_EXISTS_CHECK';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[static::$SCENARIO_WITHOUT_EXISTS_CHECK] = $scenarios[static::SCENARIO_DEFAULT];
        return $scenarios;
    }
}