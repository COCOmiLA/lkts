<?php

namespace common\components\applyingSteps;

use common\components\applyingSteps\steps\interfaces\IApplyingStep;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use Yii;





class ApplicationApplyingStep implements IApplyingStep
{
    public $name = "EMPTY_STEP";
    public $shortName;
    public $status;
    public $errors;
    


    public $application;

    const STEP_STATUS_VALID = 1;
    const STEP_STATUS_FAILED = 0;
    const STEP_STATUS_UNTOUCHED = 2;

    const STEP_QEUSTIONARY = 'questionary';
    const STEP_EXAM_RESULT = 'exam_result';
    const STEP_ADMISSION = 'admission';
    const STEP_APPLICATION = 'application';
    const STEP_SCANS = 'scans';
    const STEP_FULL_PACKAGE = 'full_package';

    public function __construct($status = null)
    {
        $this->status = $status ?? self::STEP_STATUS_UNTOUCHED;
        $this->errors = [];
    }

    




    public function execute(): bool
    {
        return false;
    }

    




    public function makeStep(): bool
    {
        $status = false;
        $this->application->refresh();

        try {
            $status = $this->execute();
        } catch (\Throwable $e) {
            $this->errors[] = "{$e->getMessage()}\n\n{$e->getTraceAsString()}";
        }

        if ($status) {
            $this->status = self::STEP_STATUS_VALID;
            $this->onSuccess();
        } else {
            $this->status = self::STEP_STATUS_FAILED;
            $this->onFail();
        }

        return $status;
    }

    


    public function onSuccess(): void
    {
        return;
    }

    


    public function onFail(): void
    {
        return;
    }

    public function getStatusMessage(): string
    {
        switch ($this->status) {
            case ApplicationApplyingStep::STEP_STATUS_VALID:
                return Yii::t(
                    'abiturient/application-applying-step',
                    'Текст сообщения об успешной отправке заявления; для менеджера отправки заявления: `Данные успешно отправлены.`'
                );
                break;
            case ApplicationApplyingStep::STEP_STATUS_FAILED:
                return Yii::t(
                    'abiturient/application-applying-step',
                    'Текст сообщения об ошибке при отправке заявления; для менеджера отправки заявления: `При отправке данных произошла ошибка.`'
                );
                break;
            case ApplicationApplyingStep::STEP_STATUS_UNTOUCHED:
                return Yii::t(
                    'abiturient/application-applying-step',
                    'Текст сообщения о не отправленном заявлении; для менеджера отправки заявления: `Данные не отправлялись.`'
                );
                break;
            default:
                return '';
                break;
        }
    }

    public function setApplication(BachelorApplication $application): void
    {
        $this->application = $application;
    }
}