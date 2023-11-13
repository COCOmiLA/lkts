<?php
namespace backend\exceptions;


use yii\web\HttpException;

class DictionaryNoDataWarningHttpException extends HttpException
{

    public function __construct($code = 0, \Exception $previous = null)
    {
        parent::__construct(400, 'no-data-warning', $code, $previous);
    }
}