<?php
namespace common\components\attachmentSaveHandler\exceptions;
use Exception;
use Throwable;





class AttachmentViolationException extends Exception
{
    


    private $fileName;

    


    private $validationErrors;

    public function __construct($fileName, $validationErrors, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->setFileName($fileName);
        $this->setErrors($validationErrors);
        parent::__construct($message, $code, $previous);
    }

    


    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    


    public function setErrors(array $errors): void
    {
        $this->validationErrors = $errors;
    }

    


    public function getFileName(): string
    {
        return $this->fileName;
    }

    


    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}