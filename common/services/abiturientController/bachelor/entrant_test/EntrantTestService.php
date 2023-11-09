<?php

namespace common\services\abiturientController\bachelor\entrant_test;

use common\components\ChangeHistoryManager;
use common\components\EntrantTestManager\EntrantTestManager;
use common\components\RegulationRelationManager;
use common\models\AttachmentType;
use common\models\dictionary\StoredReferenceType\StoredChildDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\CgetChildSubject;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\services\abiturientController\bachelor\BachelorService;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class EntrantTestService extends BachelorService
{
    




    public function getRegulationsAndAttachmentsForEntrantTest(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_EGE,
            RegulationRelationManager::RELATED_ENTITY_EGE
        );
    }

    










    public function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array
    {
        $hasChanges = false;

        if ($application->canEdit()) {
            [
                'hasError' => $hasError,
                'hasChanges' => $hasChanges,
            ] = EntrantTestManager::proceedEntrantTestFromPost($this->request, $application);

            if (!$hasError) {
                [
                    'hasChanges' => $hasChangesTmp,
                    'attachments' => $attachments,
                    'regulations' => $regulations,
                ] = parent::postProcessingRegulationsAndAttachments($application, $attachments, $regulations);

                $hasChanges |= $hasChangesTmp;
            }
        }

        return [
            'hasChanges' => $hasChanges,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    





    public function getNextStep(BachelorApplication $application, string $currentStep = 'ege-result'): string
    {
        return parent::getNextStep($application, $currentStep);
    }

    






    public function checkAttachmentFiles(
        BachelorApplication $application,
        bool                $canEdit,
        ?string             $attachmentTypeRelatedEntity = null
    ): array
    {
        return parent::checkAttachmentFiles(
            $application,
            $canEdit,
            AttachmentType::RELATED_ENTITY_EGE
        );
    }

    








    public function defineDisciplineSet(BachelorApplication $application, User $currentUser): array
    {
        $hasError = false;
        $hasChanges = false;
        $classForAlert = '';
        $messageForAlert = '';

        if ($application->canEdit()) {
            $db = EgeResult::getDb();
            $transaction = $db->beginTransaction();
            try {
                
                [
                    'hasError' => $hasError,
                    'messageForAlert' => $messageForAlert
                ] = $this->writeHistoryChanges($application, $currentUser);

                if (!$hasError) {
                    $classForAlert = 'alert alert-warning';
                    $messageForAlert = Yii::$app->configurationManager->getText('no_data_saved_text');

                    $hasChanges = EgeResult::loadFromPost($application, $this->request->post());
                    if ($hasChanges) {
                        $application->resetStatus();

                        $classForAlert = 'alert alert-success';
                        $messageForAlert = Yii::t(
                            'abiturient/bachelor/ege/all',
                            'Сообщение об успешном сохранении набора ВИ; на стр. ВИ: `Набор вступительных испытаний подтвержден.`'
                        );
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $classForAlert = 'alert alert-danger';
                    $messageForAlert = Yii::t(
                        'abiturient/bachelor/ege/all',
                        'Сообщение о не удачном заполнении набора ВИ; на стр. ВИ: `Ошибка заполнения актуальных вступительных испытаний. Обратитесь к администратору.`'
                    );

                    throw new UserException($messageForAlert);
                }
            } catch (Throwable $e) {
                $hasError = true;
                $transaction->rollBack();
                Yii::error(
                    'Критическая ошибка обработки наборов ВИ.' . PHP_EOL . print_r(['throwable' => $e->getMessage()], true),
                    'EntrantTestService.defineDisciplineSet'
                );
                throw $e;
            }
        }

        return [
            'hasError' => $hasError,
            'classForAlert' => $classForAlert,
            'messageForAlert' => $messageForAlert
        ];
    }

    




    public function solvedConflict(BachelorApplication $application): void
    {
        $deletedIds = [];
        $egeResults = $application->egeResults;

        [
            'entranceTestsCollection' => $entranceTestsCollection,
            'childrenEntranceTestsCollection' => $childrenEntranceTestsCollection
        ] = $this->getEntranceTestsCollections($application);
        foreach ($egeResults as $result) {
            

            if (in_array($result->id, $deletedIds)) {
                continue;
            }

            
            if (!$this->mainDisciplineSolvedConflict($result, $entranceTestsCollection)) {
                continue;
            }

            
            $this->childrenDisciplineSolvedConflict($result, $childrenEntranceTestsCollection);
        }
    }

    







    private function mainDisciplineSolvedConflict(EgeResult $result, array $entranceTestsCollection): bool
    {
        $isArchived = ArrayHelper::getValue($result, 'cgetDiscipline.archive', true);
        if (!$isArchived) {
            return false;
        }

        $referenceUid = ArrayHelper::getValue($result, 'cgetDiscipline.reference_uid', '');
        $newDisciplineId = ArrayHelper::getValue($entranceTestsCollection, $referenceUid);
        if (!$newDisciplineId) {
            $result->delete();
            return false;
        }
        $result->cget_discipline_id = $newDisciplineId;
        if (!$result->save()) {
            throw new RecordNotValid($result);
        }

        return true;
    }

    







    private function childrenDisciplineSolvedConflict(EgeResult $result, array $childrenEntranceTestsCollection): bool
    {
        $isArchived = ArrayHelper::getValue($result, 'cgetChildDiscipline.archive', true);
        $referenceUid = ArrayHelper::getValue($result, 'cgetChildDiscipline.reference_uid', '');
        if (
            !$isArchived ||
            empty($referenceUid) ||
            empty($childrenEntranceTestsCollection)
        ) {
            return false;
        }

        $newChildDisciplineId = ArrayHelper::getValue($childrenEntranceTestsCollection, $referenceUid);
        if (!$newChildDisciplineId) {
            $result->delete();
            return false;
        }
        $result->cget_child_discipline_id = $newChildDisciplineId;
        if (!$result->save()) {
            throw new RecordNotValid($result);
        }

        return true;
    }

    




    private function getEntranceTestsCollections(BachelorApplication $application): array
    {
        return [
            'entranceTestsCollection' => ArrayHelper::map(
                $this->getEntranceTestsCollection($application),
                'reference_uid',
                'id'
            ),
            'childrenEntranceTestsCollection' => ArrayHelper::map(
                $this->getChildrenEntranceTestsCollection($application),
                'reference_uid',
                'id'
            ),
        ];
    }

    




    private function getEntranceTestsCollection(BachelorApplication $application): array
    {
        $availableSets = $application->getAvailableCgetEntranceTestSetIds();

        $cgetEntranceTestTableName = CgetEntranceTest::tableName();
        $cgetEntranceTestSetTableName = CgetEntranceTestSet::tableName();
        $storedDisciplineReferenceTypeTableName = StoredDisciplineReferenceType::tableName();
        $disciplines = StoredDisciplineReferenceType::find()
            ->select([
                "{$storedDisciplineReferenceTypeTableName}.id",
                "{$storedDisciplineReferenceTypeTableName}.reference_uid"
            ])
            ->leftJoin($cgetEntranceTestTableName, "{$storedDisciplineReferenceTypeTableName}.id = {$cgetEntranceTestTableName}.subject_ref_id")
            ->leftJoin($cgetEntranceTestSetTableName, "{$cgetEntranceTestTableName}.cget_entrance_test_set_id = {$cgetEntranceTestSetTableName}.id")
            ->andWhere([
                "{$cgetEntranceTestTableName}.archive" => false,
                "{$cgetEntranceTestSetTableName}.archive" => false,
                "{$storedDisciplineReferenceTypeTableName}.archive" => false,
            ])
            ->andWhere(['IN', "{$cgetEntranceTestSetTableName}.id", $availableSets])
            ->groupBy([
                "{$storedDisciplineReferenceTypeTableName}.id",
                "{$storedDisciplineReferenceTypeTableName}.reference_uid"
            ])
            ->all();

        return $disciplines;
    }

    




    private function getChildrenEntranceTestsCollection(BachelorApplication $application): array
    {
        $availableSets = $application->getAvailableCgetEntranceTestSetIds();

        $cgetChildSubjectTableName = CgetChildSubject::tableName();
        $cgetEntranceTestTableName = CgetEntranceTest::tableName();
        $cgetEntranceTestSetTableName = CgetEntranceTestSet::tableName();
        $storedChildDisciplineReferenceTypeTableName = StoredChildDisciplineReferenceType::tableName();
        $disciplines = StoredChildDisciplineReferenceType::find()
            ->select([
                "{$storedChildDisciplineReferenceTypeTableName}.id",
                "{$storedChildDisciplineReferenceTypeTableName}.reference_uid"
            ])
            ->leftJoin($cgetChildSubjectTableName, "{$storedChildDisciplineReferenceTypeTableName}.id = {$cgetChildSubjectTableName}.child_subject_ref_id")
            ->leftJoin($cgetEntranceTestTableName, "{$cgetChildSubjectTableName}.cget_entrance_test_id = {$cgetEntranceTestTableName}.id")
            ->leftJoin($cgetEntranceTestSetTableName, "{$cgetEntranceTestTableName}.cget_entrance_test_set_id = {$cgetEntranceTestSetTableName}.id")
            ->andWhere([
                "{$cgetChildSubjectTableName}.archive" => false,
                "{$cgetEntranceTestTableName}.archive" => false,
                "{$cgetEntranceTestSetTableName}.archive" => false,
                "{$storedChildDisciplineReferenceTypeTableName}.archive" => false,
            ])
            ->andWhere(['IN', "{$cgetEntranceTestSetTableName}.id", $availableSets])
            ->groupBy([
                "{$storedChildDisciplineReferenceTypeTableName}.id",
                "{$storedChildDisciplineReferenceTypeTableName}.reference_uid"
            ])
            ->all();

        return $disciplines;
    }

    





    protected function writeHistoryChanges(BachelorApplication $application, User $currentUser): array
    {
        $hasError = false;
        $messageForAlert = '';

        $change = ChangeHistoryManager::persistChangeForEntity($currentUser, ChangeHistory::CHANGE_HISTORY_EXAM_SET);
        $change->application_id = $application->id;
        if (!$change->save()) {
            $hasError = true;
            $messageForAlert = Yii::t(
                'abiturient/bachelor/ege/all',
                'Сообщение о не удачном сохранении изменений; на стр. ВИ: `Ошибка записи истории изменений. Обратитесь к администратору.`'
            );
            Yii::error(
                'Ошибка записи истории изменений.' . PHP_EOL . print_r(['errors' => $change->errors], true),
                'EntrantTestService.writeHistoryChanges'
            );
        }

        return [
            'hasError' => $hasError,
            'messageForAlert' => $messageForAlert
        ];
    }
}
