<?php







namespace common\components;

use Exception;

class soapException extends Exception {
    public $message;
    public $code;
    public $action;
    public $errorMessage;
    public $params;

    public function __construct($message='', $code='', $action='', $errorMessage='', $params=[]) {
        $this->message = $message;
        $this->code = $code;
        $this->action = $action;
        $this->errorMessage = $errorMessage;
        $this->params = $params;

        parent::__construct("{$message} `{$action}` ({$errorMessage}).", $code);
    }
}
