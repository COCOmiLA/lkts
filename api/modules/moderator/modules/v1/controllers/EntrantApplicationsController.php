<?php

namespace api\modules\moderator\modules\v1\controllers;


use api\modules\moderator\modules\v1\DTO\BlockEntrantApplication\PortalEntrantApplicationWithModeratorAndStateDTO;
use api\modules\moderator\modules\v1\DTO\BlockEntrantApplication\PortalEntrantApplicationWithModeratorDTO;
use api\modules\moderator\modules\v1\DTO\FilesTransfer\GetFilesListDTO;
use api\modules\moderator\modules\v1\DTO\FilesTransfer\PostFilesListDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO\ManagerDecideActionDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDeclineEntrantApplication\ManagerDeclineEntrantApplicationDTO;
use api\modules\moderator\modules\v1\DTO\ReferenceType\CampaignRefDTO;
use api\modules\moderator\modules\v1\DTO\VerifyLastMasterServerHistoryDTO\VerifyLastMasterServerHistoryDTO;
use api\modules\moderator\modules\v1\models\EntrantApplication\decorators\EntrantApplicationModifiedViewDecorated;
use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantApplication;
use api\modules\moderator\modules\v1\models\MasterServerHistory;
use api\modules\moderator\modules\v1\repositories\EntrantApplication\EntrantApplicationRepository;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\models\errors\RecordNotValid;
use common\models\SendingFile;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models\ApplicationAcceptDeclineModel;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\FilesManager;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\ReceivingFile;
use common\modules\abiturient\models\services\FullPackageFilesSyncer;
use geoffry304\enveditor\exceptions\FileNotFoundException;
use ReflectionException;
use SimpleXMLElement;
use stdClass;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class EntrantApplicationsController extends BaseApiController
{
    public $modelClass = EntrantApplication::class;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'get-modified-entrant-applications',
                            'get-entrant-application',
                            'accept-application',
                            'decline-application',
                            'block-entrant-application',
                            'release-entrant-application',
                            'get-entrant-application-files-list',
                            'post-entrant-application-files-list',
                            'get-file-part',
                            'put-file-part',
                            'notify-application-being-removed',
                        ],
                        'allow' => true,
                        'verbs' => [
                            'POST'
                        ]
                    ],
                ],
            ],
        ];
    }

    






    public function actionGetModifiedEntrantApplications()
    {
        $dto = new CampaignRefDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());
        $ref = $dto->getStoredReferenceType();

        $query = EntrantApplicationRepository::GetReadyEntrantApplicationListByCampaignReferenceTypeQuery($ref);

        return $query->all();
    }

    










    public function actionGetEntrantApplication()
    {
        $dto = new PortalEntrantApplicationWithModeratorDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        $application = $dto->getPropertyPortalEntrantApplication()->getApplication();
        $dto->getPropertyManager()->authorizeMasterSystemManager();

        if (is_null($application)) {
            throw new NotFoundHttpException('В базах портала не найдено подходящее заявление.');
        }

        [$blocked, $_] = $application->isApplicationBlocked();
        if ($blocked) {
            throw new ForbiddenHttpException("Данное заявление заблокировано модератором ({$application->getBlockerName()})");
        }

        
        $result = (new FullApplicationPackageBuilder($application))
            ->build();
        return array_merge($result, [
            'ApplicationState' => $application->status == ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL ? 'DocumentsReturn' : 'DocumentsApply',
        ]);
    }

    











    public function actionAcceptApplication()
    {
        $dto = new ManagerDecideActionDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        
        $dto->getPropertyManager()->authorizeMasterSystemManager();

        $user = $dto->getPropertyEntrantPackage()->getUser();
        
        $application = $dto->getPropertyEntrantPackage()->getApplication($user);

        $application->checkApplicationBlocked();

        
        $dto->getPropertyEntrantPackage()->updateApplication($application);

        $application->status = ApplicationInterface::STATUS_APPROVED;
        $application->draft_status = IDraftable::DRAFT_STATUS_APPROVED;
        $application->unblockApplication(false);
        $application->moderator_comment = $dto->getPropertyManagerComment();
        if (!$application->save()) {
            throw new RecordNotValid($application);
        }

        ApplicationHistory::deleteAll(['application_id' => $application->id]);
        $application->addModerateHistory(\Yii::$app->user->identity);

        $comment = $application->moderator_comment;
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

        DraftsManager::clearOldModerations($application, \Yii::$app->user->identity, DraftsManager::REASON_APPROVED);
        DraftsManager::clearOldSendings($application, \Yii::$app->user->identity, DraftsManager::REASON_APPROVED);
        DraftsManager::removeOldApproved($application, \Yii::$app->user->identity, DraftsManager::REASON_APPROVED);

        $application->type->toggleResubmitPermissions($application->user, false);

        \Yii::$app->notifier->notifyAboutApplyApplication($application->user_id, $comment);
    }

    













    public function actionDeclineApplication()
    {
        $dto = new ManagerDeclineEntrantApplicationDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        
        $dto->getPropertyManager()->authorizeMasterSystemManager();
        
        $application = $dto->getPropertyPortalEntrantApplication()->getApplication();

        $application->checkApplicationBlocked();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $questionary = $application->abiturientQuestionary;

            $application->status = ApplicationInterface::STATUS_NOT_APPROVED;
            $application->draft_status = IDraftable::DRAFT_STATUS_SENT;
            $application->unblockApplication(false);
            $application->moderator_comment = $dto->getPropertyManagerComment();
            $application->approved_at = null;

            if (!$application->save()) {
                throw new RecordNotValid($application);
            }
            $application->addModerateHistory(\Yii::$app->user->identity);
            ApplicationHistory::deleteAll(['application_id' => $application->id]);
            if (!$application->user->userRef) {
                $questionary->status = AbiturientQuestionary::STATUS_NOT_APPROVED;
            }
            $questionary->save();

            $change = new ApplicationAcceptDeclineModel();
            $change->application = $application;
            $change->application_action_status = ApplicationAcceptDeclineModel::APPLICATION_REJECT;
            $change->application_comment = $application->moderator_comment;

            $change->getChangeHistoryHandler()->getInsertHistoryAction()->proceed();

            
            $application = DraftsManager::createArchivePoint(
                $application,
                DraftsManager::REASON_DECLINED,
                IDraftable::DRAFT_STATUS_SENT
            );


            DraftsManager::clearOldModerations($application, \Yii::$app->user->identity, DraftsManager::REASON_DECLINED);
            DraftsManager::clearOldSendings($application, \Yii::$app->user->identity, DraftsManager::REASON_DECLINED);

            \Yii::$app->notifier->notifyAboutDeclineApplication($application->user_id, $application->moderator_comment);

            $application->type->toggleResubmitPermissions($application->user, true);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    


    public function actionBlockEntrantApplication()
    {
        $dto = new PortalEntrantApplicationWithModeratorDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        
        $manager = $dto->getPropertyManager()->getManager();
        $dto->getPropertyManager()->authorizeMasterSystemManager();

        $application = $dto->getPropertyPortalEntrantApplication()->getApplication();

        if (!$application->checkApplicationBlocked()) {
            $application->blockApplication($manager->getEntrantManagerEntity());
        }
    }

    


    public function actionReleaseEntrantApplication()
    {
        $dto = new PortalEntrantApplicationWithModeratorDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        $dto->getPropertyManager()->authorizeMasterSystemManager();

        $application = $dto->getPropertyPortalEntrantApplication()->getApplication([IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_APPROVED]);

        $application->fullyUnblockApplication();
    }

    public function actionGetEntrantApplicationFilesList()
    {
        $dto = new GetFilesListDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        $application = $dto->getPropertyEntrant()->getApplication();
        $dto->getPropertyManager()->authorizeMasterSystemManager();

        if (is_null($application)) {
            throw new NotFoundHttpException('В базах портала не найдено подходящее заявление.');
        }

        $files_syncer = new FullPackageFilesSyncer($application);
        $files_info_list = $files_syncer->getApplicationFilesInfo();

        return FullPackageFilesSyncer::BuildFilesInfoToList($files_info_list);
    }

    public function actionPostEntrantApplicationFilesList()
    {
        $dto = new PostFilesListDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        $application = $dto->getPropertyEntrant()->getApplication();
        $dto->getPropertyManager()->authorizeMasterSystemManager();

        if (is_null($application)) {
            throw new NotFoundHttpException('В базах портала не найдено подходящее заявление.');
        }

        [$blocked, $_] = $application->isApplicationBlocked();
        if ($blocked) {
            throw new ForbiddenHttpException("Данное заявление заблокировано модератором ({$application->getBlockerName()})");
        }

        $files_syncer = new FullPackageFilesSyncer($application);
        $missing_files = [];
        $files_info_list = $files_syncer->getApplicationFilesInfo();
        foreach ($dto->getFiles() as $file_from_1c) {
            if (!FullPackageFilesSyncer::FindInfoBy1CFile($files_info_list, $file_from_1c)) {
                $missing_files[] = $file_from_1c;
            }
        }

        return array_map(function (stdClass $item) {
            return json_decode(json_encode($item), true);
        }, $missing_files);
    }

    public function actionGetFilePart()
    {
        $request = new SimpleXMLElement($this->getRequestBodyManager()->getRequestBody());

        $transfer_id = $request->TransferId;
        $part_number = $request->PartNumber;
        $file_ext = $request->FileExt;
        $file_hash = $request->FileHash;
        $file_uid = $request->FileUID;

        
        $file = FilesManager::FindFileWithExistingContentWithoutFileNameCheck(
            $file_ext,
            $file_hash,
            null
        );
        if (!$file) {
            throw new FileNotFoundException();
        }

        $part = SendingFile::SplitFile($file->getFileContent(), $transfer_id)[$part_number - 1];
        return [
            'TransferId' => (string)$transfer_id,
            'PartNumber' => (integer)$part_number,
            'PartData' => base64_encode((string)$part->part_bin),
        ];
    }

    public function actionPutFilePart()
    {
        $request = new SimpleXMLElement($this->getRequestBodyManager()->getRequestBody());
        
        if (!$request->PartData) {
            throw new UserException("Нет содержимого файла");
        }
        return [
            'PartFileName' => ReceivingFile::StoreFileDataToTempFile(base64_decode($request->PartData))
        ];
    }

    public function actionNotifyApplicationBeingRemoved()
    {
        $dto = new PortalEntrantApplicationWithModeratorAndStateDTO();
        $dto->setStringRawData($this->getRequestBodyManager()->getRequestBody());

        $dto->getPropertyManager()->authorizeMasterSystemManager();

        $application = $dto->getPropertyPortalEntrantApplication()->getApplication();

        if ($dto->getIsSuccess()) {
            try {
                $application->markApplicationRemoved();

                return [
                    'Complete' => true,
                    'Description' => ''
                ];
            } catch (Throwable $e) {
                return [
                    'Complete' => false,
                    'Description' => $e->getMessage()
                ];
            }
        } else {
            try {
                $application->status = ApplicationInterface::STATUS_REJECTED_BY1C;
                $application->moderator_comment = $dto->getComment();
                if (!$application->save()) {
                    throw new UserException("Не удалось сохранить заявление");
                }
                return [
                    'Complete' => true,
                    'Description' => ''
                ];
            } catch (Throwable $e) {
                return [
                    'Complete' => false,
                    'Description' => $e->getMessage()
                ];
            }
        }
    }
}