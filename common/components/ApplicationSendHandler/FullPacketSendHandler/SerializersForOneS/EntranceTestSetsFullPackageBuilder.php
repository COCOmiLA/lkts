<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\EntrantTestManager\EntrantTestManager;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorEntranceTestSet;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EgeResult;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class EntranceTestSetsFullPackageBuilder extends BaseApplicationPackageBuilder
{
    
    protected $speciality;

    public function __construct(BachelorApplication $application, ?BachelorSpeciality $speciality = null)
    {
        parent::__construct($application);

        if ($speciality) {
            $this->speciality = $speciality;
        }
    }

    public function build()
    {
        $tn = BachelorEntranceTestSet::tableName();

        $bachelorEntranceTestSets = $this
            ->speciality
            ->getBachelorEntranceTestSets()
            ->orderBy("{$tn}.priority")
            ->all();
        if (EmptyCheck::isEmpty($bachelorEntranceTestSets)) {
            return [];
        }

        $tnEgeResult = EgeResult::tableName();
        $results = [];
        foreach ($bachelorEntranceTestSets as $setTest) {
            

            
            $egeResult = $setTest->getEgeResult()
                ->andWhere(["{$tnEgeResult}.application_id" => $this->application->id])
                ->one();

            $parentSubjectRef = ReferenceTypeManager::getEmptyRefTypeArray();
            $subjectRef = ReferenceTypeManager::GetReference(
                $egeResult,
                'cgetDiscipline'
            );
            if ($egeResult->hasChildren()) {
                $parentSubjectRef = $subjectRef;
                $subjectRef = ReferenceTypeManager::GetReference(
                    $egeResult,
                    'cgetChildDiscipline'
                );
            }

            $results[] = [
                'SubjectRef' => $subjectRef,
                'EntranceTestResultSourceRef' => ReferenceTypeManager::GetReference(
                    $egeResult,
                    'cgetExamForm'
                ),
                'ParentSubjectRef' => $parentSubjectRef,
                'Priority' => $setTest->priority,
            ];
        }

        return $results;
    }

    














    public function update($raw_data): bool
    {
        $testSetNotToArchiveId = [];
        $egeAttributesNotToArchive = [];
        foreach ($raw_data as $data) {
            [
                'speciality' => $this->speciality,
                'EntranceTests' => $entranceTests,
            ] = $data;

            if (!is_array($entranceTests) || ArrayHelper::isAssociative($entranceTests)) {
                $entranceTests = [$entranceTests];
            }

            foreach ($entranceTests as $entranceTest) {
                $entranceTest = ToAssocCaster::getAssoc($entranceTest);

                $priority = (int)$entranceTest['Priority'];
                $entranceTestResultSourceRefId = ReferenceTypeManager::GetOrCreateReference(
                    StoredDisciplineFormReferenceType::class,
                    $entranceTest['EntranceTestResultSourceRef']
                )->id;

                $childrenSubjectRefId = 0;
                $subjectRefId = ReferenceTypeManager::GetOrCreateReference(
                    StoredDisciplineReferenceType::class,
                    $entranceTest['SubjectRef']
                )->id;

                $isParentSubjectRefEmpty = EmptyCheck::isEmpty(
                    $entranceTest['ParentSubjectRef']
                );
                $isParentSubjectReferenceTypeEmpty = ReferenceTypeManager::isReferenceTypeEmpty(
                    $entranceTest['ParentSubjectRef']
                );
                if (!$isParentSubjectRefEmpty && !$isParentSubjectReferenceTypeEmpty) {
                    $childrenSubjectRefId = $subjectRefId;
                    $subjectRefId = ReferenceTypeManager::GetOrCreateReference(
                        StoredDisciplineReferenceType::class,
                        $entranceTest['ParentSubjectRef']
                    )->id;
                }

                [$set, $_] = EntrantTestManager::getOrCreateEntrantTestSet(
                    $this->application,
                    $this->speciality,
                    $subjectRefId,
                    $childrenSubjectRefId,
                    $entranceTestResultSourceRefId,
                    $priority
                );

                if (!in_array($set->entrance_test_junction, $egeAttributesNotToArchive)) {
                    $egeAttributesNotToArchive[] = $set->entrance_test_junction;
                }
                if (!in_array($set->id, $testSetNotToArchiveId)) {
                    $testSetNotToArchiveId[] = $set->id;
                }
            }
        }

        EntrantTestManager::archiveNotActualEntranceTestSetWithReadOnly($this->application, $testSetNotToArchiveId);
        EntrantTestManager::archiveNotActualEgeWithReadOnly($this->application, $egeAttributesNotToArchive);

        return true;
    }
}
