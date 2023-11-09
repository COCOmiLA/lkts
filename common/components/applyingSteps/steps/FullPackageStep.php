<?php


namespace common\components\applyingSteps\steps;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\ErrorMessageAnalyzer;
use common\components\soapException;
use common\models\EmptyCheck;
use yii\base\UserException;

class FullPackageStep extends ApplicationApplyingStep
{
    public $shortName = self::STEP_FULL_PACKAGE;

    public $name = 'Отправка заявления';


    public function execute(): bool
    {
        $package_array = (new FullApplicationPackageBuilder($this->application))
            ->sendFiles()
            ->build();
        try {
            $response = \Yii::$app->soapClientWebApplication->load(
                'PostEntrantPackage',
                ['EntrantPackage' => $package_array]
            );
            $this->application->clearApplicationCache();

        } catch (soapException $e) {
            \Yii::error('Ошибка при вызове PostEntrantPackage: ' . $e->getMessage());
            throw ErrorMessageAnalyzer::getCustomException($e);
        }
        \Yii::$app->soapClientAbit->resetCurrentUserCache('GetReference', [$this->application->user_id]); 
        if (isset($response->return) && isset($response->return->UniversalResponse)) {
            if ($response->return->UniversalResponse->Complete) {
                $this->application->archiveMarkedAgreementsToDelete();
                $this->application->setQuestionaryAsApproved();
                if (isset($response->return->EntrantPackageType) && !EmptyCheck::isEmpty($response->return->EntrantPackageType)) {
                    (new FullApplicationPackageBuilder($this->application))
                        ->setUpdateSentAt(true)
                        ->updateUserRefByFullPackage($response->return->EntrantPackageType) 
                        ->receiveFiles()
                        ->update($response->return->EntrantPackageType);
                }
                return true;
            } else {
                $e = new UserException($response->return->UniversalResponse->Description ?? '');
                throw ErrorMessageAnalyzer::getCustomException($e);
            }
        }
        return false;
    }
}
