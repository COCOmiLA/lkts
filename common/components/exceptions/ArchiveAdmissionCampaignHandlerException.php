<?php


namespace common\components\exceptions;


use Throwable;
use yii\base\UserException;





class ArchiveAdmissionCampaignHandlerException extends UserException
{
    





    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Вы пытаетесь взаимодействовать с неактуальной (архивной) приемной кампанией. Обратитесь к администратору.', $code, $previous);
    }
}