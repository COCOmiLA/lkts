<?php

namespace common\modules\abiturient\models\services;

use common\models\settings\ApplicationsSettings;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;

class NextStepService
{
    private User $user;
    private BachelorApplication $application;

    private ?bool $use_next_step_forwarding = null;

    public function __construct(BachelorApplication $app)
    {
        $this->application = $app;
        $this->user = $app->user;
    }

    public function getUseNextStepForwarding(): bool
    {
        if ($this->use_next_step_forwarding === null) {
            $this->use_next_step_forwarding = ApplicationsSettings::getValueByName('move_step_forward_on_form_submit') === '1';
        }
        return $this->use_next_step_forwarding;
    }

    public function getNextStep(string $current_step): string
    {
        $steps = array_keys($this->getFilteredStepsBySettings());
        $current_step_index = array_search($current_step, $steps);
        if ($current_step_index === false) {
            return $current_step;
        }
        if ($current_step_index + 1 >= count($steps)) {
            return $current_step;
        }
        $next_step = $steps[$current_step_index + 1];
        if ($this->user->canMakeStep($next_step, $this->application) && $this->passesAdditionalChecks($next_step, $this->application)) {
            return $next_step;
        }
        return $current_step;
    }

    public function getUrlByStep(string $step): array
    {
        $steps = $this->getFilteredStepsBySettings();
        if (!isset($steps[$step])) {
            return [''];
        }
        return $steps[$step];
    }

    private function passesAdditionalChecks(string $step, BachelorApplication $application): bool
    {
        $additional_checks = $this->getAdditionalChecks();
        if (isset($additional_checks[$step])) {
            return $additional_checks[$step]($application);
        }
        return true;
    }

    private function getSteps(): array
    {
        return [
            'education' => ['/bachelor/education', 'id' => $this->application->id],
            'accounting-benefits' => ['/bachelor/accounting-benefits', 'id' => $this->application->id],
            'specialities' => ['/bachelor/application', 'id' => $this->application->id],
            'ege-result' => ['/bachelor/ege', 'id' => $this->application->id],
            'ia-list' => ['/abiturient/ialist', 'id' => $this->application->id],
            'load-scans' => ['/bachelor/load-scans', 'id' => $this->application->id],
            'make-comment' => ['/bachelor/comment', 'id' => $this->application->id],
        ];
    }

    private function getAdditionalChecks(): array
    {
        return [
            'accounting-benefits' => function (BachelorApplication $application): bool {
                return !$application->getNotFilledRequiredEducationScanTypeIds();
            },
            'specialities' => function (BachelorApplication $application): bool {
                return !$application->getNotFilledRequiredBenefitsScanTypeIds();
            },
            'ege-result' => function (BachelorApplication $application): bool {
                return !$application->getNotFilledRequiredSpecialitiesScanTypeIds();
            },
            'ia-list' => function (BachelorApplication $application): bool {
                return !$application->getNotFilledRequiredExamsScanTypeIds();
            },
            'make-comment' => function (BachelorApplication $application): bool {
                return !$application->getNotFilledRequiredEducationScanTypeIds()
                    && !$application->getNotFilledRequiredBenefitsScanTypeIds()
                    && !$application->getNotFilledRequiredSpecialitiesScanTypeIds()
                    && !$application->getNotFilledRequiredExamsScanTypeIds();
            },

        ];
    }

    



    private function getFilteredStepsBySettings(): array
    {
        $steps = $this->getSteps();
        $filteredSteps = [];
        foreach ($steps as $key => $step) {
            if ($this->user->canViewStep($key, $this->application)) {
                $filteredSteps[$key] = $step;
            }
        }
        return $filteredSteps;
    }
}