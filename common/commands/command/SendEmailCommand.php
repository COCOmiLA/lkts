<?php

namespace common\commands\command;

use trntv\tactician\base\BaseCommand;




class SendEmailCommand extends BaseCommand
{
    


    public $from;
    


    public $to;
    


    public $subject;
    


    public $view;
    


    public $params;
    


    public $body;
    


    public $html = true;

    


    public function init()
    {
        parent::init();
        $this->from = $this->from ?: \Yii::$app->params['robotEmail'];
    }

    


    public function isHtml()
    {
        return (bool) $this->html;
    }
}
