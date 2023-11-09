<?php
namespace common\components\ReferenceTypeManager\exceptions;

use common\modules\student\models\ReferenceType;





class ReferenceManagerValidationException extends \Exception {
    private $referenceErrors = [];

    


    private $rawData;

    public function __construct($referenceErrors, ReferenceType $rawData, $code = 0, \Throwable $previous = null)
    {
        $this->referenceErrors = $referenceErrors;
        $this->rawData = $rawData;
        parent::__construct("Ошибка сохранения данных о ReferenceType.\n\nПолученные данные:\n". print_r($this->rawData->toObject(), true) ."\n\nОшибки возникшие при сохранении модели:\nОбратите внимание, что поля ReferenceName (колонка \"Наименование\" в запрашиваемом справочнике) обязательно к заполнению." . print_r($this->referenceErrors, true), $code, $previous);
    }

    


    public function getReferenceErrors(): array
    {
        return $this->referenceErrors;
    }
}