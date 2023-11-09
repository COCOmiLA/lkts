<?php
namespace api\modules\moderator\modules\v1\DTO\exceptions;

use Exception;
use Throwable;

class DTOFieldNotFoundException extends Exception
{
    



    private $field = '';

    public function __construct($field, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->field = $field;
        parent::__construct("Ошибка сериализации XML данных. Не найдено поле: {$this->field}" . $message, $code, $previous);
    }
}