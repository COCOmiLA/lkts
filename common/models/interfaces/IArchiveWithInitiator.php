<?php

namespace common\models\interfaces;

use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\models\EntrantManager;
use common\models\User;

interface IArchiveWithInitiator extends ArchiveModelInterface
{
    




    public function setArchiveInitiator($initiator): IArchiveWithInitiator;

    public function setArchiveReason(string $reason): IArchiveWithInitiator;

    


    public function getArchiveInitiator();

    


    public function getArchiveInitiatorName();
}
