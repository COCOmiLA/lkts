<?php


namespace common\models\interfaces;


interface ArchiveModelInterface extends IArchiveQueryable
{
    




    public function archive(): bool;

    



    public static function getArchivedAtColumn(): string;

    public function isArchive(): bool;
}
