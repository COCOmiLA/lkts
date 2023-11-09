<?php


namespace common\models\interfaces;







interface AttachmentLinkableApplicationEntity extends AttachmentLinkableEntity
{
    public static function getApplicationIdColumn(): string;
}