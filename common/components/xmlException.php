<?php







namespace common\components;

use Exception;

class xmlException extends Exception {
    public $message;
    public $code;
    public $url;
    public $errorMessage;

    public function __construct($message='', $code='', $url='', $errorMessage='') {
        $this->message = $message;
        $this->code = $code;
        $this->url = $url;
        $this->errorMessage = $errorMessage;

        parent::__construct($message, $code);
    }
}
