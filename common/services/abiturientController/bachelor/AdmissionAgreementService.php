<?php

namespace common\services\abiturientController\bachelor;

use common\commands\command\AddToTimelineCommand;
use common\components\ChangeHistoryManager;
use common\components\configurationManager;
use common\components\exceptions\AdmissionAgreementException;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\repositories\BachelorSpecialityRepository;
use common\services\abiturientController\bachelor\BachelorService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Request;
use yii\web\UploadedFile;

class AdmissionAgreementService extends BachelorService
{
    protected $commandBus;

    



    public function __construct(
        Request $request,
        configurationManager $configurationManager
    ) {
        $this->request = $request;
        $this->configurationManager = $configurationManager;
        $this->commandBus = Yii::$app->commandBus;
    }

    





    public function checkAgreementAccessibility(BachelorApplication $application, BachelorSpeciality $speciality): array
    {
        if (
            $application->haveAttachedAgreementExcludeNonBudget() &&
            !$speciality->isCommercialBasis()
        ) {
            return [
                'canEdited' => false,
                'consentAddErrors' => Yii::t(
                    'abiturient/bachelor/admission-agreement/all',
                    'Текст ошибки дублировании согласия на зачисление, на странице НП: `Невозможно прикрепить согласие, так как в системе уже есть информация о прикрепленном согласии на зачисление.`'
                ),
            ];
        }

        if (!$speciality->canAddAgreements()) {
            return [
                'canEdited' => false,
                'consentAddErrors' => Yii::t(
                    'abiturient/bachelor/admission-agreement/all',
                    'Текст ошибки о блокировке согласия на зачисление, на странице НП: `Невозможно прикрепить согласие, так как запрещено редактировать согласие на зачисление.`'
                ),
            ];
        }

        if (!$speciality->checkAgreementConditions()) {
            return [
                'canEdited' => false,
                'consentAddErrors' => Yii::t(
                    'abiturient/bachelor/admission-agreement/all',
                    'Текст ошибки о блокировке согласия на зачисление, на странице НП: `Невозможно прикрепить согласие на зачисление для данного основания поступления, обратитесь в приёмную кампанию.`'
                ),
            ];
        }

        return [
            'canEdited' => true,
            'consentAddErrors' => '',
        ];
    }

    




    protected function uploadAgreement(int $specialityId): ?AdmissionAgreement
    {
        $admissionAgreement = new AdmissionAgreement();
        $admissionAgreement->load($this->request->post());
        $admissionAgreement->speciality_id = $specialityId;
        $admissionAgreement->setScenario(AdmissionAgreement::SCENARIO_NEW_AGREEMENT);
        $admissionAgreement->file = UploadedFile::getInstance($admissionAgreement, 'file');

        if (!$admissionAgreement->upload()) {
            return null;
        }

        return $admissionAgreement;
    }

    protected function copyAgreement(AdmissionAgreement $agreement, BachelorSpeciality $toSpeciality): ?AdmissionAgreement
    {
        $admissionAgreement = new AdmissionAgreement();
        $admissionAgreement->speciality_id = $toSpeciality->id;
        $admissionAgreement->setScenario(AdmissionAgreement::SCENARIO_NEW_AGREEMENT);
        if (!$admissionAgreement->save()) {
            throw new RecordNotValid($admissionAgreement);
        }
        $admissionAgreement->link('linkedFile', $agreement->linkedFile);

        return $admissionAgreement;
    }

    public function createAgreements(User $user, BachelorApplication $application, BachelorSpeciality $speciality): void
    {
        $addedAgreements = [];
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$admissionAgreement = $this->uploadAgreement($speciality->id)) {
                throw new AdmissionAgreementException("Не удалось создать согласие");
            }

            $addedAgreements[] = $admissionAgreement;

            if (ArrayHelper::getValue($application, 'type.campaign.use_common_agreements', false) && $speciality->isInAgreementConditions()) {
                foreach ($this->getSpecialitiesByAgreementConditions($application, $speciality) as $other_spec) {
                    if (!$other_spec->agreement) {
                        $addedAgreements[] = $this->copyAgreement($admissionAgreement, $other_spec);
                    }
                }
            }

            foreach ($addedAgreements as $addedAgreement) {
                $addToTimelineCommandConfig = $this->changeAgreementHistoryProcess(
                    $user,
                    $application,
                    $speciality,
                    $addedAgreement
                );
                $this->commandBus->handle(new AddToTimelineCommand($addToTimelineCommandConfig));
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    




    public function copyAgreementToAddedSpecialities(BachelorApplication $application, array $added_specialities): void
    {
        if (!ArrayHelper::getValue($application, 'type.campaign.use_common_agreements', false)) {
            return;
        }

        $conditions =  $application->type->campaign->agreementConditions ?? [];
        $edu_source_ref_uids = ArrayHelper::getColumn($conditions, 'educationSourceRef.reference_uid');
        $existing_spec = $application->getSpecialities()
            ->innerJoinWith('agreement', true)
            ->joinWith('speciality.educationSourceRef edu_source_ref', false)
            ->andWhere(['edu_source_ref.reference_uid' => $edu_source_ref_uids]) 
            ->one();

        if (!$existing_spec) {
            return;
        }

        foreach ($added_specialities as $added_speciality) {
            if ($added_speciality->isInAgreementConditions()) {
                $this->copyAgreement($existing_spec->agreement, $added_speciality);
            }
        }
    }

    


    protected function getSpecialitiesByAgreementConditions(BachelorApplication $application, BachelorSpeciality $speciality): array
    {
        $conditions =  $application->type->campaign->agreementConditions ?? [];
        $edu_source_ref_uids = ArrayHelper::getColumn($conditions, 'educationSourceRef.reference_uid');
        return BachelorSpecialityRepository::GetSpecialitiesByAgreementConditons($speciality, $edu_source_ref_uids)->all();
    }

    




    protected function createAgreementDecline(AdmissionAgreement $agreement, File $file): AgreementDecline
    {
        $agreementDecline = new AgreementDecline();
        $agreementDecline->agreement_id = $agreement->id;
        $agreementDecline->archive = false;
        $agreementDecline->file = $file;
        if (!$agreementDecline->upload()) {
            Yii::error(
                'Ошибки валидации при отзыве согласия согласия' .
                    ($agreement->hasErrors() ? "\nAgreement\n" .
                        print_r($agreement->errors, true) : '') .
                    ($agreementDecline->hasErrors() ? "\nAgreementDecline\n" .
                        print_r($agreementDecline->errors, true) : ''),
                'AdmissionAgreementService.uploadAgreementDecline'
            );
            throw new RecordNotValid($agreementDecline);
        }

        return $agreementDecline;
    }

    public function declineAgreements(User $user, BachelorApplication $application, AdmissionAgreement $agreement)
    {
        $agreementsToDecline = [$agreement];

        if (ArrayHelper::getValue($application, 'type.campaign.use_common_agreements', false)) {
            $agreements = AdmissionAgreement::find()
                ->innerJoinWith('speciality bachelor_spec', true)
                ->andWhere([AdmissionAgreement::tableName() . '.archive' => false])
                ->andWhere(['!=', AdmissionAgreement::tableName() . '.status', AdmissionAgreement::STATUS_MARKED_TO_DELETE])
                ->andWhere(['!=', AdmissionAgreement::tableName() . '.id', $agreement->id])
                ->andWhere(['bachelor_spec.application_id' => $application->id])
                ->all();

            foreach ($agreements as $activeAgreement) {
                if ($activeAgreement->speciality->isInAgreementConditions()) {
                    $agreementsToDecline[] = $activeAgreement;
                }
            }
        }

        $file = null;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($agreementsToDecline as $agreementToDecline) {
                if ($agreementToDecline->status === AdmissionAgreement::STATUS_NOTVERIFIED) {
                    $addToTimelineCommandConfig = $this->changeAgreementDeclineHistoryProcess(
                        $user,
                        $application,
                        $agreementToDecline->speciality,
                        $agreementToDecline
                    );
                    $this->commandBus->handle(new AddToTimelineCommand($addToTimelineCommandConfig));
                } elseif ($agreementToDecline->status = AdmissionAgreement::STATUS_VERIFIED) {
                    if (!$file) {
                        $tmpDecline = new AgreementDecline();
                        $tmpDecline->agreement_id = $agreement->id;
                        $uploaded_file = UploadedFile::getInstance($tmpDecline, 'file');
                        $file = File::GetOrCreateByTempFile(
                            $tmpDecline->getPathToStoreFiles(),
                            $uploaded_file
                        );
                    }

                    $this->createAgreementDecline($agreementToDecline, $file);
                    if (!$agreementToDecline->makeDeclined()) {
                        throw new AdmissionAgreementException("Не удалось отозвать согласие");
                    }

                    $addToTimelineCommandConfig = $this->changeAgreementDeclineHistoryProcess(
                        $user,
                        $application,
                        $agreementToDecline->speciality,
                        $agreementToDecline,
                    );
                    $this->commandBus->handle(new AddToTimelineCommand($addToTimelineCommandConfig));
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage(), 'declineAgreements');
            throw $e;
        }
    }

    







    public function changeAgreementHistoryProcess(
        User $user,
        BachelorApplication $application,
        BachelorSpeciality $speciality,
        AdmissionAgreement $admissionAgreement
    ): array {
        $application->addApplicationHistory(ApplicationHistory::TYPE_AGREEMENT_CHANGED);

        return $this->changeHistoryProcess(
            $user,
            $application,
            $speciality,
            $admissionAgreement,
            'application_add_consent',
            ChangeHistory::CHANGE_HISTORY_NEW_AGREEMENT
        );
    }

    







    public function changeAgreementDeclineHistoryProcess(
        User $user,
        BachelorApplication $application,
        BachelorSpeciality $speciality,
        AdmissionAgreement $admissionAgreement
    ): array {
        $admissionAgreement->archive();

        return $this->changeHistoryProcess(
            $user,
            $application,
            $speciality,
            $admissionAgreement,
            'application_return_consent',
            ChangeHistory::CHANGE_HISTORY_AGREEMENT_DECLINE
        );
    }

    









    private function changeHistoryProcess(
        User $user,
        BachelorApplication $application,
        BachelorSpeciality $speciality,
        AdmissionAgreement $admissionAgreement,
        string $event,
        int $changeHistoryType
    ): array {
        
        
        $application->resetStatus();
        $change = ChangeHistoryManager::persistChangeForEntity($user, $changeHistoryType);
        $change->application_id = $application->id;
        if (!$change->save()) {
            throw new RecordNotValid($change);
        }

        $class = ChangeHistoryManager::persistChangeHistoryEntity($admissionAgreement, ChangeHistoryEntityClass::CHANGE_TYPE_INSERT);
        $class->setChangeHistory($change);
        if (!$class->save()) {
            throw new RecordNotValid($class);
        }

        return [
            'category' => 'abiturient',
            'event' => $event,
            'data' => [
                'public_identity' => $user->getPublicIdentity(),
                'user_id' => $user->getId(),
                'campaign' => $application->type->campaignName,
                'speciality' => $speciality->speciality->directionRef->reference_name ?? '',
            ]
        ];
    }
}
