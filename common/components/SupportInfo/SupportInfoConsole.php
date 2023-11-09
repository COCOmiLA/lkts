<?php

namespace common\components\SupportInfo;

use Yii;
use yii\helpers\Console;

class SupportInfoConsole extends BaseSupportInfo
{
    public function print(array $params = []): void
    {
        $message = $this->formatMessage($this->render($params));
        if (PHP_SAPI == 'cli') {
            Console::stderr($message . "\n");
        } else {
            echo $message;
        }
    }

    public function render(array $params = []): string
    {
        if (!$this->showDeveloperInfo()) {
            return '';
        }
        return $this->getSupportInfo($params);
    }

    public function showDeveloperInfo(): bool
    {
        return true;
    }

    protected function getSupportInfo(array $params = []): string
    {
        return <<<MESSAGE
Возникла ошибка. Сообщите о проблеме на {$this->email}. В письме:
- подробно опишите проблему
- скопируйте текст сообщения об ошибке, представленный ниже
- укажите номер СЛК
- прикрепите лог-файл из каталога {$this->logDir}
- прикрепите скриншот окна с подробной информацией о программе из "1С:Университет ПРОФ"
MESSAGE;
    }

    protected function formatMessage($message, $format = [Console::FG_RED, Console::BOLD])
    {
        $stream = (PHP_SAPI === 'cli') ? \STDERR : \STDOUT;
        if (Yii::$app->controller instanceof \yii\console\Controller && Yii::$app->controller->isColorEnabled($stream)
            || Yii::$app instanceof \yii\console\Application && Console::streamSupportsAnsiColors($stream)) {
            $message = Console::ansiFormat($message, $format);
        }

        return $message;
    }
}