<?php


namespace common\models\interfaces;


use yii\db\ActiveQuery;






interface AttachmentInterface
{
    



    public function getFileDownloadUrl(): ?string;

    public function getFileDeleteUrl(bool $make_redirect = false): ?string;

    



    public function getExtension(): ?string;

    public function getAttachmentTypeName(): string;

    




    public function getEntity(): ?ActiveQuery;
}