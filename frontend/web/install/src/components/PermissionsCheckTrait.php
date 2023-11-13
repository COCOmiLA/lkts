<?php

trait PermissionsCheckTrait
{
    protected function ensureIsWritable($path)
    {
        if (!is_writable($path)) {
            throw new \Exception("Нет доступа на запись или не найден целевой путь ({$path}). "
            . "Обратитесь к администратору для предоставления доступа для системного пользователя " . $this->getPhpUser());
        }
    }
    
    protected function ensureIsReadable($path)
    {
        if (!is_readable($path)) {
            throw new \Exception("Нет доступа на чтение или не найден целевой путь ({$path}). "
            . "Обратитесь к администратору для предоставления доступа для системного пользователя " . $this->getPhpUser());
        }
    }
    
    protected function getPhpUser()
    {
        return get_current_user();
    }
}