<?php

namespace common\components\EntrantTestManager;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\DictionaryPredmetOfExamsSchedule;
use common\models\dictionary\Speciality;
use common\models\EmptyCheck;
use common\models\errors\RecordNotFound;
use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\bachelor\BachelorDatePassingEntranceTest;
use common\modules\abiturient\models\bachelor\EgeResult;
use stdClass;
use Yii;
use yii\base\UserException;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class ExamsScheduleManager extends BaseEntrantTestManager
{
    





    public static function buildStructureTo1C(EgeResult $entrantTest, $speciality = null): array
    {
        
        $bachelorDatePassingEntranceTest = $entrantTest->bachelorDatePassingEntranceTest;

        if (EmptyCheck::isEmpty($bachelorDatePassingEntranceTest)) {
            return [];
        }

        $dateTime = $bachelorDatePassingEntranceTest->dateTimeOfExamsSchedule;
        $DateTimesArray = ExamsScheduleManager::buildDateTimes($bachelorDatePassingEntranceTest);

        $tnSpeciality = Speciality::tableName();
        $tnDictionaryPredmetOfExamsSchedule = DictionaryPredmetOfExamsSchedule::tableName();
        $predmetOfExamsList = $dateTime->getPredmetOfExamsSchedules();
        
        
        
        if ($speciality && $speciality instanceof Speciality) {
            $predmetOfExamsList = $predmetOfExamsList->leftJoin(
                $tnSpeciality,
                "{$tnDictionaryPredmetOfExamsSchedule}.campaign_ref_id = {$tnSpeciality}.campaign_ref_id AND " .
                "{$tnDictionaryPredmetOfExamsSchedule}.curriculum_ref_id = {$tnSpeciality}.curriculum_ref_id AND " .
                "{$tnDictionaryPredmetOfExamsSchedule}.group_ref_id = {$tnSpeciality}.competitive_group_ref_id AND " .
                "{$tnDictionaryPredmetOfExamsSchedule}.finance_ref_id = {$tnSpeciality}.education_source_ref_id"
            )
                ->andWhere(["{$tnSpeciality}.id" => $speciality->id]);
        }
        $predmetOfExamsList = $predmetOfExamsList->all();

        $examsSchedule = [];
        foreach ($predmetOfExamsList as $predmetOfExams) {
            $temp = ExamsScheduleManager::makeArrayClone($DateTimesArray);
            $examsSchedule[] = [
                'PredmetGUID' => $predmetOfExams->predmet_guid,
                'CampaignRef' => ReferenceTypeManager::GetReference($predmetOfExams->campaignRef),
                'CurriculumRef' => ReferenceTypeManager::GetReference($predmetOfExams->curriculumRef),
                'FinanceRef' => ReferenceTypeManager::GetReference($predmetOfExams->financeRef),
                'FormRef' => ReferenceTypeManager::GetReference($predmetOfExams->formRef),
                'GroupRef' => ReferenceTypeManager::GetReference($predmetOfExams->groupRef),
                'SubjectRef' => ReferenceTypeManager::GetReference($predmetOfExams->subjectRef),
                'DateTimes' => $temp,
            ];
        }

        return $examsSchedule;
    }

    




    private static function buildDateTimes(BachelorDatePassingEntranceTest $bachelorDatePassingEntranceTest): array
    {
        $dateTime = $bachelorDatePassingEntranceTest->dateTimeOfExamsSchedule;

        if (EmptyCheck::isEmpty($dateTime)) {
            return [];
        }

        $entrantTest = [[
            'DateTimeGUID' => $dateTime->guid_date_time,
            'StartDate' => date(ExamsScheduleManager::DATE_FORMAT_FOR_1C, $dateTime->start_date),
            'EndDate' => date(ExamsScheduleManager::DATE_FORMAT_FOR_1C, $dateTime->end_date),
            'RegistrationDate' => date(ExamsScheduleManager::DATE_FORMAT_FOR_1C, $dateTime->registration_date),
            'EventTypeRef' => ReferenceTypeManager::GetReference($dateTime->eventTypeRef),
        ]];

        $children = $bachelorDatePassingEntranceTest->children;
        if ($children) {
            $entrantTest = ArrayHelper::merge(
                $entrantTest,
                ExamsScheduleManager::buildDateTimes($children)
            );
        }

        return $entrantTest;
    }

    





    public static function updateStructureTo1C(EgeResult $entrantTest, array $rawDatas): bool
    {
        if (!is_array($rawDatas) || ArrayHelper::isAssociative($rawDatas)) {
            $rawDatas = [$rawDatas];
        }

        $dateTimeIdDotToArchive = [];

        foreach ($rawDatas as $rawData) {
            

            if (is_array($rawData)) {
                $rawData = (object)$rawData;
            }

            if ($rawData->DateTimes) {
                if (!is_array($rawData->DateTimes) || ArrayHelper::isAssociative($rawData->DateTimes)) {
                    $rawData->DateTimes = [$rawData->DateTimes];
                }

                $parentBachelorDateTime = new BachelorDatePassingEntranceTest();
                foreach ($rawData->DateTimes as $dateTime) {
                    

                    if (is_array($dateTime)) {
                        $dateTime = (object)$dateTime;
                    }

                    $parentBachelorDateTime = ExamsScheduleManager::getOrCreateDateTimeChildren($entrantTest, $parentBachelorDateTime, $dateTime);
                    $dateTimeIdDotToArchive[] = $parentBachelorDateTime->id;
                }
            }
        }

        $tnBachelorDatePassingEntranceTest = BachelorDatePassingEntranceTest::tableName();
        $dateTimeToArchive = $entrantTest->getBachelorDatePassingEntranceTest()
            ->andWhere(['NOT IN', "{$tnBachelorDatePassingEntranceTest}.id", $dateTimeIdDotToArchive])
            ->all();

        ExamsScheduleManager::archiveNotActualData($dateTimeToArchive);
        return true;
    }

    





    public static function archiveDateTime(
        EgeResult $entrantTest,
        array     $egeNotToArchive
    ) {
        $tnBachelorDatePassingEntranceTest = BachelorDatePassingEntranceTest::tableName();
        $toArchive = $entrantTest->getBachelorDatePassingEntranceTest()
            ->andWhere(['NOT IN', "{$tnBachelorDatePassingEntranceTest}.id", $egeNotToArchive])
            ->all();

        ExamsScheduleManager::archiveNotActualData($toArchive);
    }

    




    public static function archiveNotActualData(array $dataToArchive)
    {
        if (!empty($dataToArchive)) {
            $transaction = Yii::$app->db->beginTransaction();

            foreach ($dataToArchive as $toArchive) {
                

                if (!$toArchive->archive()) {
                    $transaction->rollBack();
                    $className = get_class($toArchive);
                    throw new UserException("Ошибка архивирования {$className}.");
                }
            }

            $transaction->commit();
        }
    }

    





    private static function getOrCreateDateTime(EgeResult $entrantTest, array $query = []): BachelorDatePassingEntranceTest
    {
        $dateTime = $entrantTest->getBachelorDatePassingEntranceTest()
            ->joinWith('dateTimeOfExamsSchedule')
            ->andWhere($query)
            ->one();
        if (!$dateTime) {
            $examsSchedule = DictionaryDateTimeOfExamsSchedule::find()
                ->where($query)
                ->active()
                ->one();
            if (!$examsSchedule) {
                throw new RecordNotFound("Ошибка. Не найден элемент расписания: " . print_r($query, true));
            }

            $dateTime = new BachelorDatePassingEntranceTest();
            $dateTime->bachelor_egeresult_id = $entrantTest->id;
            $dateTime->date_time_of_exams_schedule_id = $examsSchedule->id;

            if (!$dateTime->save()) {
                throw new RecordNotValid($dateTime);
            }
        }

        return $dateTime;
    }

    





    private static function getOrCreateDateTimeByDateTimeGuid(EgeResult $entrantTest, stdClass $rawData): BachelorDatePassingEntranceTest
    {
        $tnDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tablename();
        $query = ["{$tnDateTimeOfExamsSchedule}.guid_date_time" => $rawData->DateTimeGUID];
        return ExamsScheduleManager::getOrCreateDateTime($entrantTest, $query);
    }

    






    private static function getOrCreateDateTimeChildren(EgeResult $entrantTest, BachelorDatePassingEntranceTest $parent, stdClass $rawData): BachelorDatePassingEntranceTest
    {
        $children = ExamsScheduleManager::getOrCreateDateTimeByDateTimeGuid($entrantTest, $rawData);
        $children->parent_id = $parent->id;
        if (!$children->save()) {
            throw new RecordNotValid($children);
        }

        return $children;
    }

    




    public static function archiveIfExist($datePassing): bool
    {
        if (!$datePassing) {
            return true;
        }

        return $datePassing->archive();
    }

    








    public static function proceedDatePassingEntranceTestFromPost(
        Request     $request,
        EgeResult   $egeResult,
        bool        $canChangeDateExamFrom1C,
        Transaction $transaction,
        array      &$msgBox = null
    ): array {
        $hasChanges = false;

        
        $egeDatePass = $egeResult->getOrCreateBachelorDatePassingEntranceTest();
        if (!$canChangeDateExamFrom1C && $egeDatePass->from_1c) {
            return [
                'hasError' =>  false,
                'hasChanges' => $hasChanges,
            ];
        }

        $postDataDatePass = ExamsScheduleManager::postDataExtractor($request->post(), "{$egeDatePass->formName()}.{$egeResult->id}");
        if ($postDataDatePass) {
            if ($egeDatePass->load($postDataDatePass, '')) {
                $parentId = $egeDatePass->id;
                if (empty($parentId)) {
                    $parentId = 'new';
                }
                $egeDatePass->from_1c = false;
                if ($egeDatePass->validate()) {
                    if (($hasChanges = $egeDatePass->hasChangedAttributes()) && !$egeDatePass->save(false)) {
                        ExamsScheduleManager::errorMessageRecorder(
                            Yii::t(
                                'abiturient/bachelor/exams-schedule-manager/all',
                                'Сообщение о не успешном сохранении расписания сдачи ВИ; на стр. ВИ: `Ошибка сохранения даты сдачи вступительного испытания.`'
                            ),
                            [],
                            'proceedCentralizedTestingFromPost',
                            $msgBox
                        );

                        $transaction->rollBack();
                        return [
                            'hasError' =>  true,
                            'hasChanges' => $hasChanges,
                        ];
                    }
                } else {
                    ExamsScheduleManager::errorMessageRecorder(
                        Yii::t(
                            'abiturient/bachelor/exams-schedule-manager/all',
                            'Сообщение о не успешном сохранении расписания сдачи ВИ; на стр. ВИ: `Ошибка валидации даты сдачи вступительного испытания.`'
                        ),
                        $egeDatePass->errors,
                        'proceedCentralizedTestingFromPost',
                        $msgBox
                    );

                    $transaction->rollBack();
                    return [
                        'hasError' =>  true,
                        'hasChanges' => $hasChanges,
                    ];
                }

                [
                    'hasError' =>  $hasError,
                    'hasChanges' => $hasChangesTmp,
                ] =  ExamsScheduleManager::proceedChildrenDatePassingEntranceTestFromPost(
                    $request,
                    $egeResult,
                    $egeDatePass,
                    $parentId,
                    $transaction,
                    $msgBox
                );

                return [
                    'hasError' => $hasError,
                    'hasChanges' => $hasChanges | $hasChangesTmp,
                ];
            }
        } elseif (!$egeDatePass->isNewRecord) {
            return [
                'hasError' =>  !$egeDatePass->archive(),
                'hasChanges' => $hasChanges,
            ];
        }

        return [
            'hasError' =>  false,
            'hasChanges' => $hasChanges,
        ];
    }

    









    private static function proceedChildrenDatePassingEntranceTestFromPost(
        Request                         $request,
        EgeResult                       $egeResult,
        BachelorDatePassingEntranceTest $egeDatePass,
        string                          $parentId,
        Transaction                     $transaction,
        array                          &$msgBox = null
    ): array {
        $hasChanges = false;
        $postDataDatePass = ExamsScheduleManager::postDataExtractor($request->post(), "{$egeDatePass->formName()}.{$egeResult->id}.{$parentId}");
        $childEgeDatePass = $egeDatePass->children;
        if (empty($postDataDatePass) && $childEgeDatePass) {
            $childEgeDatePass->delete();
        } else {
            $childEgeDatePass = $egeDatePass;
            while (!empty($postDataDatePass)) {
                $tmpParentId = $childEgeDatePass->id;
                $childEgeDatePass = $childEgeDatePass->getOrCreateChildren();
                if ($childEgeDatePass->load($postDataDatePass, '')) {
                    if (empty($childEgeDatePass->id)) {
                        $parentId .= '.new';
                        $childEgeDatePass->parent_id = $tmpParentId;
                    } else {
                        $parentId .= ".{$childEgeDatePass->id}";
                    }

                    $childEgeDatePass->from_1c = false;
                    if ($childEgeDatePass->validate()) {
                        if (($hasChanges |= $childEgeDatePass->hasChangedAttributes()) && !$childEgeDatePass->save(false)) {
                            $substrCount = substr_count((string)$parentId, '.');
                            if ($substrCount <= 1) {
                                $message = Yii::t(
                                    'abiturient/bachelor/exams-schedule-manager/all',
                                    'Сообщение о не успешном сохранении подрасписания сдачи ВИ; на стр. ВИ: `Ошибка сохранения зависимой даты сдачи вступительного испытания.`'
                                );
                            } else {
                                $message = Yii::t(
                                    'abiturient/bachelor/exams-schedule-manager/all',
                                    'Сообщение о не успешном сохранении подрасписания сдачи ВИ; на стр. ВИ: `Ошибка сохранения зависимой({substrCount}) даты сдачи вступительного испытания.`',
                                    ['substrCount' => $substrCount]
                                );
                            }
                            ExamsScheduleManager::errorMessageRecorder(
                                $message,
                                [],
                                'proceedCentralizedTestingFromPost',
                                $msgBox
                            );

                            $transaction->rollBack();
                            return [
                                'hasError' =>  true,
                                'hasChanges' => $hasChanges,
                            ];
                        }
                    } else {
                        ExamsScheduleManager::errorMessageRecorder(
                            Yii::t(
                                'abiturient/bachelor/exams-schedule-manager/all',
                                'Сообщение о не успешном сохранении подрасписания сдачи ВИ; на стр. ВИ: `Ошибка валидации даты сдачи вступительного испытания.`'
                            ),
                            $childEgeDatePass->errors,
                            'proceedCentralizedTestingFromPost',
                            $msgBox
                        );

                        $transaction->rollBack();
                        return [
                            'hasError' =>  true,
                            'hasChanges' => $hasChanges,
                        ];
                    }
                }
                $postDataDatePass = ExamsScheduleManager::postDataExtractor($request->post(), "{$egeDatePass->formName()}.{$egeResult->id}.{$parentId}");
                $childEgeDatePass = $egeDatePass->children;
                if (empty($postDataDatePass) && $childEgeDatePass) {
                    $childEgeDatePass->delete();
                }
            }
        }

        return [
            'hasError' =>  false,
            'hasChanges' => $hasChanges,
        ];
    }
}
