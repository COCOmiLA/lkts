<?php
namespace common\components\EntrantModeratorManager\interfaces;


use common\models\EntrantManager;






interface IEntrantManager
{
    public function getEntrantManagerEntity(): EntrantManager;

    




    public function getEntrantManager();
}