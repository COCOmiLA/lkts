<?php

namespace common\models;


use common\components\changeHistoryHandler\QuestionaryActiveRecordChangeHistoryHandler;

class AbiturientAvatar extends Attachment
{

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->setChangeHistoryHandler(new QuestionaryActiveRecordChangeHistoryHandler($this));
    }

    public function formName()
    {
        return 'AbiturientAvatar';
    }
}
