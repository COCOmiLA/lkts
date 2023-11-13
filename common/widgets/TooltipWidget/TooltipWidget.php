<?php

namespace common\widgets\TooltipWidget;

use common\assets\TooltipAsset;
use common\models\EmptyCheck;

class TooltipWidget extends \yii\bootstrap4\Widget
{
    public $message;
    public $params;
    public $placement = 'top';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->message = trim((string)$this->message, " \t\n\r\0\x0B\"");
        $this->message = str_replace('"', "'", $this->message);
        $this->message = str_replace('`', "'", $this->message);
    }

    public function run()
    {
        if (!EmptyCheck::isEmpty($this->message)) {
            $view = $this->getView();
            TooltipAsset::register($view);

            return $this->render('@common/widgets/TooltipWidget/views/tooltip', [
                'message' => $this->message,
                'placement' => $this->placement,
                'params' => $this->params,
            ]);
        }
        return '';
    }
}