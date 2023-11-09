<?php


namespace common\models\interfaces;

use common\models\AttachmentType;
use common\models\User;
use yii\db\ActiveQuery;
use yii\db\TableSchema;







interface AttachmentLinkableEntity
{
    public static function getTableLink(): string;

    public static function getEntityTableLinkAttribute(): string;

    public static function getAttachmentTableLinkAttribute(): string;

    public static function getModel(): string;

    public static function getDbTableSchema(): TableSchema;

    public function getAttachmentType(): ?AttachmentType;

    public function getRawAttachments(): ActiveQuery;

    public function getAttachments(): ActiveQuery;

    public function getName(): string;

    public function getAttachmentCollection(): ?FileToShowInterface;

    public function getAttachmentConnectors(): array;

    public function getUserInstance(): User;

    




    public function getIsActuallyNewRecord(): bool;
}