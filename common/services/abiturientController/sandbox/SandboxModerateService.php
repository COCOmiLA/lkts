<?php

namespace common\services\abiturientController\sandbox;

use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\authentication1CManager;
use common\components\configurationManager;
use common\components\ErrorMessageAnalyzer;
use common\components\notifier\notifier;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models\ApplicationAcceptDeclineModel;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\PassportData;
use common\services\abiturientController\BaseService;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class SandboxModerateService extends BaseService
{
    
    private notifier $notifier;

    
    private authentication1CManager $authentication1CManager;

    public function __construct(
        Request                 $request,
        configurationManager    $configurationManager,
        notifier                $notifier,
        authentication1CManager $authentication1CManager
    )
    {
        parent::__construct($request, $configurationManager);

        $this->notifier = $notifier;
        $this->authentication1CManager = $authentication1CManager;
    }

    




    public function getApplicationById(int $id): ?BachelorApplication
    {
        return BachelorApplication::findOne($id);
    }

    








    public function getValidatedEgeResults(BachelorApplication $application): array
    {
        $validationErrors = [];
        $haveValidationEgeErrors = false;

        
        $egeResults = $application->getSavedEgeResults();
        foreach ($egeResults as $result) {
            if (!$result->validate()) {
                $haveValidationEgeErrors = true;
                $key = Yii::t(
                    'sandbox/moderate/all',
                    'Заголовок ошибки валидации ВИ, на странице проверки заявления: `Предмет - {DISCIPLINE_NAME}`',
                    ['DISCIPLINE_NAME' => ArrayHelper::getValue(
                        $result,
                        'cgetDiscipline.reference_name',
                        Yii::t(
                            'sandbox/moderate/all',
                            'Текст ВИ когда не удалось определить предмет, на странице проверки заявления: `Невозможно получить наименование дисциплины.`'
                        )
                    )]
                );
                $validationErrors[$key] = $result->errors;
            }
        }

        return [
            'egeResults' => $egeResults,
            'validationErrors' => $validationErrors,
            'haveValidationEgeErrors' => $haveValidationEgeErrors
        ];
    }

    




    public function checkDraftStatusToModerate(BachelorApplication $application): ?int
    {
        if ($application->draft_status == $application->getDraftStatusToModerate()) {
            return null;
        }

        $moderatingApp = DraftsManager::getOrCreateApplicationDraftByOtherDraft(
            $application,
            $application->getDraftStatusToModerate()
        );

        return $moderatingApp->id;
    }

    





    public function startModeratingProcess(User $currentUser, BachelorApplication $application): void
    {
        if (!$this->request->isPost) {
            $application->setLastManager($currentUser->id);
            $application->blockApplication($currentUser->getEntrantManagerEntity());
        }
    }

    




    public function validatePassports(array $passports): array
    {
        $passportErrors = [];
        if (!$passports) {
            $passportErrors[] = [[Yii::t(
                'sandbox/moderate/all',
                'Текст ошибки пустого паспорта, на странице проверки заявления: `Не указано ни одного документа удостоверяющего личность.`'
            )]];
        }

        foreach ($passports as $passport) {
            if ($passport->validate()) {
                continue;
            }

            $resultArray = [];
            foreach ($passport->errors as $attribute) {
                foreach ($attribute as $error) {
                    $resultArray[] = Yii::t(
                        'sandbox/moderate/all',
                        'Заголовок ошибки валидации паспорта, на странице проверки заявления: `Ошибка проверки реквизитов документа, удостоверяющего личность, по причине «{VALIDATION_ERROR}»`',
                        ['VALIDATION_ERROR' => $error]
                    );
                }
            }
            $passportErrors[] = [$resultArray];
        }

        return $passportErrors;
    }

    





    public function checkBalls(BachelorApplication $application, array $specialities): array
    {
        $checkErrors = [];
        $haveValidationErrors = false;

        if (!$application->type->enable_check_ege) {
            return [
                'checkErrors' => $checkErrors,
                'haveValidationErrors' => $haveValidationErrors,
            ];
        }

        foreach ($specialities as $speciality) {
            $checkError = $speciality->checkBalls();
            if ($checkError !== null) {
                $checkErrors[] = $checkError;
            }
        }

        return [
            'checkErrors' => $checkErrors,
            'haveValidationErrors' => $haveValidationErrors,
        ];
    }

    





    public function validateAgreement(BachelorApplication $application, array $specialities): bool
    {
        $validateAgreementDate = true;
        $applicationSentAt = $application->sent_at;

        foreach ($specialities as $speciality) {
            $speciality->setScenario(BachelorSpeciality::SCENARIO_FULL_VALIDATION);
            if (!$speciality->validateAgreementDate($applicationSentAt)) {
                $validateAgreementDate = false;
                break;
            }

            $speciality->getAvailableCategories();
        }

        return $validateAgreementDate;
    }

    




    public function validateSpecialities(array $specialities): array
    {
        $specialtyErrors = [];

        foreach ($specialities as $specialty) {
            $specialty->setScenario(BachelorSpeciality::SCENARIO_FULL_VALIDATION);
            if ($specialty->validate()) {
                continue;
            }

            $errors = $specialty->errors;
            $errors['name'] = $specialty->speciality->getFullName();

            $specialtyErrors[] = $errors;
        }

        return $specialtyErrors;
    }

    





    public function afterSuccessFullApplicationApprovement(User $currentUser, BachelorApplication $application): void
    {
        $lastManagerId = ArrayHelper::getValue(Yii::$app, 'user.identity.id');
        $application->setLastManager($lastManagerId);
        $application->save(true, ['last_manager_id', 'last_management_at']);
        $comment = $application->moderator_comment;
        $this->notifier->notifyAboutApplyApplication($application->user_id, $comment);

        $change = new ApplicationAcceptDeclineModel();
        $change->application = $application;
        $change->application_action_status = ApplicationAcceptDeclineModel::APPLICATION_ACCEPTED;
        $change->application_comment = $comment;

        $change->getChangeHistoryHandler()->getInsertHistoryAction()->proceed();

        
        $application = DraftsManager::createArchivePoint(
            $application,
            DraftsManager::REASON_APPROVED,
            IDraftable::DRAFT_STATUS_APPROVED
        );

        
        DraftsManager::clearOldSendings($application, $currentUser, DraftsManager::REASON_APPROVED);
        DraftsManager::clearOldModerations($application, $currentUser, DraftsManager::REASON_APPROVED);
        DraftsManager::removeOldApproved($application, $currentUser, DraftsManager::REASON_APPROVED);

        $application->type->toggleResubmitPermissions($application->user, false);
    }

    




    public function afterFailureOnApplicationApprovement(BachelorApplication $application): BachelorApplication
    {
        $applyingSteps = $application->applyingSteps;
        $application = DraftsManager::createArchivePoint(
            $application,
            DraftsManager::REASON_REJECTED_BY_1C,
            $application->draft_status
        );

        $application->applyingSteps = $applyingSteps; 

        return $application;
    }

    




    public function getReallySentApplicationAndQuestionary(BachelorApplication $application): array
    {
        $reallySentApplication = null;
        $reallySentQuestionary = null;
        if (!$application->type->persist_moderators_changes_in_sent_application && $application->draft_status == IDraftable::DRAFT_STATUS_MODERATING) {
            
            $reallySentApplication = DraftsManager::getApplicationDraft($application->user, $application->type, IDraftable::DRAFT_STATUS_SENT);
            if ($reallySentApplication) {
                
                $reallySentQuestionary = $reallySentApplication->linkedAbiturientQuestionary;
            }
        }

        return [
            'reallySentApplication' => $reallySentApplication,
            'reallySentQuestionary' => $reallySentQuestionary,
        ];
    }

    







    public function checkPersonDuplicates(AbiturientQuestionary $questionary): array
    {
        $abiturientDoubles = [];
        $doublesForParents = [];

        
        if (!$questionary->user->userRef || !$questionary->user->hasApprovedApps()) {
            $abiturientDoubles = $this->authentication1CManager->getAbiturientDoublesByFullInfo($questionary->getEntityForDuplicatesFind());
        }

        
        if ($questionary->parentData) {
            foreach ($questionary->parentData as $parent) {
                if ($parent->parentRef || !$parent->personalData) {
                    continue;
                }
                $parent_duples = $this->authentication1CManager->getAbiturientDoublesByFullInfo($parent->getEntityForDuplicatesFind());
                if ($parent_duples) {
                    $doublesForParents[$parent->id] = $parent_duples;
                }
            }
        }


        return [
            'abiturientDoubles' => $abiturientDoubles,
            'doublesForParents' => $doublesForParents,
        ];
    }

    public function composeApplyingStepsErrors(BachelorApplication $application, ?string $additionalError): array
    {
        $result = [];
        foreach ($application->applyingSteps as $step) {
            $result[] = [
                'name' => $step->name,
                'shortName' => $step->shortName,
                'status' => $step->status,
                'statusMessage' => $step->getStatusMessage(),
                'errors' => $step->errors,
            ];

        }
        if ($additionalError) {
            $result[] = [
                'name' => 'Обработка данных о принятых заявлениях',
                'shortName' => 'after_approve',
                'status' => ApplicationApplyingStep::STEP_STATUS_FAILED,
                'statusMessage' => Yii::t(
                    'abiturient/application-applying-step',
                    'Текст сообщения об ошибке после отправки заявления; для менеджера: `При обработке данных произошла ошибка.`'
                ),
                'errors' => [
                    $additionalError
                ],
            ];
        }
        return $result;
    }
}
