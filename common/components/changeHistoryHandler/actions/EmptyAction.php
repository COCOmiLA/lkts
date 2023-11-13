<?php
namespace common\components\changeHistoryHandler\actions;



class EmptyAction extends BaseAction
{
    public function proceed(): bool
    {
        return true;
    }
}