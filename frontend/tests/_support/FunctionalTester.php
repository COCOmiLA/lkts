<?php

namespace frontend\tests;
















class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;


    public function seeValidationError($message)
    {
        $this->see($message, '.invalid-feedback');
    }

    public function dontSeeValidationError($message)
    {
        $this->dontSee($message, '.invalid-feedback');
    }
}
