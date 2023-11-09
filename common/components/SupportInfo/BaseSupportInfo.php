<?php

namespace common\components\SupportInfo;

use Yii;
use yii\base\Component;




abstract class BaseSupportInfo extends Component implements ICanPrintSupportInfo
{
    public $email = '1c@sgu-infocom.ru';

    public function getLogDir(): string
    {
        return Yii::getAlias('@base') . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    }
}