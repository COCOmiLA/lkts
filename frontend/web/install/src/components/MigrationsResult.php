<?php

class MigrationsResult
{
    public $complete;
    public $message;
    
    



    public function __construct($complete, $message)
    {
        $this->complete = $complete;
        $this->message = $message;
    }
}
