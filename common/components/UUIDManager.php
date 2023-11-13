<?php

namespace common\components;

use Ramsey\Uuid\Uuid;

class UUIDManager
{
    public static function GetUUID(): string
    {
        return Uuid::uuid4()->toString();
    }
}
