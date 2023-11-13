<?php

class ErrorHandler
{
    public function handle()
    {
        $lastError = error_get_last();
        if ($lastError) {
            http_response_code(400);
            echo "<br>" . $lastError['message'];
            die();
        }
    }
}