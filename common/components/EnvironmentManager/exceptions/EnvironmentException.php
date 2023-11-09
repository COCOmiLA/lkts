<?php
namespace common\components\EnvironmentManager\exceptions;


use Exception;
use Throwable;

class EnvironmentException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct("Работа портала вуза не обеспечивается с выбранным окружением. Вы можете принудительно отключить обработку этого исключения установив значение <strong><em>false</em></strong> для поля <em>ENABLE_ENVIRONMENT_CHECK</em> в конфигурационном файле .env в корне портала.", $code, $previous);
    }
}