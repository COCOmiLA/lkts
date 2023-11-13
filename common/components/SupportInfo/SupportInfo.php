<?php

namespace common\components\SupportInfo;

class SupportInfo extends BaseSupportInfo
{
    public function print(array $params = []): void
    {
        echo $this->render($params);
    }

    public function render(array $params = []): string
    {
        if (!$this->showDeveloperInfo()) {
            return '';
        }
        return \Yii::$app->view->renderFile(realpath(__DIR__) . '/views/supportInfo.php', $params);
    }

    public function showDeveloperInfo(): bool
    {
        try {
            if (!\backend\models\CommonSettings::getInstance()->show_technical_info_on_error) {
                return false;
            }
            $currentUser = \Yii::$app->user->identity;
            if (!$currentUser) {
                return false;
            }
            $transferUser = $currentUser->getTransferUser();
            if ($transferUser) {
                $currentUser = $transferUser;
            }
            if (!$currentUser->isInternalRole()) {
                return false;
            }
            return false;
        } catch (\Throwable $e) {
            \Yii::error("Ошибка при проверке допуска к подробной информации об ошибках: " . $e->getMessage(), 'DeveloperInfo');
            return false;
        }
    }
}