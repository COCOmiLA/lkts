<?php

namespace common\services\abiturientController\questionary;

use common\components\AttachmentManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\PassportData;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;



class PassportDataService extends AbiturientQuestionaryService
{
    





    public function setPassportData(User $currentUser, AbiturientQuestionary $questionary): void
    {
        $path = (new PassportData())->formName() . '.id';
        $passport = $this->getPassportData($currentUser, $questionary, $path);

        $this->processFromPost($currentUser, $passport);
    }

    





    public function deletePassportData(User $currentUser, AbiturientQuestionary $questionary): void
    {
        $passport = $this->getPassportData($currentUser, $questionary, 'passportId');

        if ($passport && !$passport->read_only) {
            $passport->archive();
            $currentUser->resetApplicationStatuses();
        }
    }

    




    public function renderPassports($questionary): array
    {
        $canEdit = $questionary->canEditQuestionary();

        $passports = new ActiveDataProvider([
            'query' => $questionary->getPassportData()
        ]);

        return [
            'passports' => $passports,
            'canEdit' => $canEdit,
        ];
    }

    






    private function getPassportData(
        User $currentUser,
        AbiturientQuestionary $questionary,
        string $path
    ): PassportData {
        $passport = new PassportData();
        $passport->questionary_id = $questionary->id;

        $id = ArrayHelper::getValue($this->request->post(), $path);

        if (!$id) {
            return $passport;
        }
        $passport = PassportData::findOne($id);
        $this->checkAccessibility($currentUser, $passport->questionary_id);

        return $passport;
    }

    








    private function processFromPost(
        User         $currentUser,
        PassportData $passport
    ): void {
        if ($passport->load($this->request->post())) {
            $db = PassportData::getDb();
            $transaction = $db->beginTransaction();
            try {
                if ($passport->notFoundContractor) {
                    $passport->contractor_id = ContractorManager::Upsert($this->request->post('Contractor'), $passport->documentType)->id;
                }

                if (!$passport->save()) {
                    throw new RecordNotValid($passport);
                }
                $attachedFileHashList = $passport->buildAttachmentHash();
                AttachmentManager::handleAttachmentUpload([$passport->attachmentCollection]);

                if (!$passport->checkIfDocumentIsChanged($attachedFileHashList)) {
                    $passport->setDocumentCheckStatusNotVerified();
                    $passport->save(['document_check_status_ref_id']);
                }
                $currentUser->resetApplicationStatuses();

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }
}
