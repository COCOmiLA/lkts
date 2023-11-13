<?php

namespace common\components\EntrantTestManager;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseApplicationPackageBuilder;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DictionaryReasonForExam;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\StoredReferenceType\SpecialRequirementReferenceType;
use common\models\dictionary\StoredReferenceType\StoredChildDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorEntranceTestSet;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EgeResult;
use stdClass;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class EntrantTestManager extends BaseEntrantTestManager
{
    


    public static function REFERENCE_ID_FIELDS(): array
    {
        return [
            ForeignLanguage::class . '_LanguageRef' => 'language_id',
            StoredDisciplineReferenceType::class . '_SubjectRef' => 'cget_discipline_id',
            StoredDisciplineReferenceType::class . '_ParentSubjectRef' => 'cget_discipline_id',
            SpecialRequirementReferenceType::class . '_SpecialRequirementRef' => 'special_requirement_ref_id',
            SpecialRequirementReferenceType::class . '_SpecialRequirementsRefs' => 'special_requirement_ref_id',
            StoredDisciplineFormReferenceType::class . '_EntranceTestTypeRef' => 'cget_exam_form_id',
            StoredDisciplineFormReferenceType::class . '_EntranceTestResultSourceRef' => 'cget_exam_form_id',
            StoredChildDisciplineReferenceType::class . '_SubjectRef' => 'cget_child_discipline_id',
        ];
    }

    


    public static function REFERENCE_UID_FIELDS(): array
    {
        return [
            ForeignLanguage::class . '_LanguageRef' => ForeignLanguage::tableName() . '.ref_key',
            StoredDisciplineReferenceType::class . '_SubjectRef' => StoredDisciplineReferenceType::tableName() . '.reference_uid',
            StoredCurriculumReferenceType::class . '_CurriculumRef' => StoredCurriculumReferenceType::tableName() . '.reference_uid',
            StoredDisciplineReferenceType::class . '_ParentSubjectRef' => StoredDisciplineReferenceType::tableName() . '.reference_uid',
            SpecialRequirementReferenceType::class . '_SpecialRequirementRef' => SpecialRequirementReferenceType::tableName() . '.reference_uid',
            SpecialRequirementReferenceType::class . '_SpecialRequirementsRefs' => SpecialRequirementReferenceType::tableName() . '.reference_uid',
            StoredDisciplineFormReferenceType::class . '_EntranceTestTypeRef' => StoredDisciplineFormReferenceType::tableName() . '.reference_uid',
            StoredDisciplineFormReferenceType::class . '_EntranceTestResultSourceRef' => StoredDisciplineFormReferenceType::tableName() . '.reference_uid',
            StoredChildDisciplineReferenceType::class . '_SubjectRef' => StoredChildDisciplineReferenceType::tableAlias() . '.reference_uid',
            StoredCompetitiveGroupReferenceType::class . '_CompetitiveGroupRef' => StoredCompetitiveGroupReferenceType::tableName() . '.reference_uid',
        ];
    }

    






    public static function proceedEntrantTestFromPost(
        Request             $request,
        BachelorApplication $application,
        array               &$msgBox = null
    ): array
    {
        $hasError = false;
        $hasChanges = false;
        $enableDatePickerForExam = ArrayHelper::getValue($application, 'type.allow_pick_dates_for_exam', false);
        $canChangeDateExamFrom1C = ArrayHelper::getValue($application, 'type.can_change_date_exam_from_1c', false);
        $hasCorrectCitizenship = BachelorResultCentralizedTesting::hasCorrectCitizenship($application);

        $transaction = Yii::$app->db->beginTransaction();

        $egeResults = $application->egeResults;
        if (!empty($egeResults)) {
            foreach ($egeResults as $egeResult) {
                

                $postData = ArrayHelper::getValue(
                    $request->post(),
                    "{$egeResult->formName()}.{$egeResult->id}"
                );
                if ($egeResult->load($postData, '')) {
                    $egeResult->status = EgeResult::STATUS_NOTVERIFIED;
                    if ($egeResult->validate()) {
                        if (($hasChanges |= $egeResult->hasChangedAttributes()) && !$egeResult->save(false)) {
                            EntrantTestManager::errorMessageRecorder(
                                Yii::t(
                                    'abiturient/bachelor/ege/all',
                                    'Сообщение о не успешном сохранении результатов ВИ; на стр. ВИ: `Ошибка сохранения вступительных испытаний.`'
                                ),
                                [],
                                'proceedEntrantTestFromPost',
                                $msgBox
                            );

                            $hasError = true;
                            $transaction->rollBack();
                            break;
                        }
                        try {
                            $egeResult->application->addApplicationHistory(ApplicationHistory::TYPE_EXAM_CHANGED);

                            if (!Yii::$app->user->identity->isModer()) {
                                $application->resetStatus();
                            }
                        } catch (Throwable $e) {
                            $hasError = true;
                            $transaction->rollBack();
                            break;
                        }

                        if ($egeResult->isExam()) {
                            if ($hasCorrectCitizenship) {
                                [
                                    'hasError' => $hasError,
                                    'hasChanges' => $hasChangesTmp,
                                ] = !CentralizedTestingManager::proceedCentralizedTestingFromPost(
                                    $request,
                                    $egeResult,
                                    $transaction,
                                    $msgBox
                                );

                                $hasChanges |= $hasChangesTmp;
                            }

                            if (!$hasError && $enableDatePickerForExam) {
                                [
                                    'hasError' => $hasError,
                                    'hasChanges' => $hasChangesTmp,
                                ] = !ExamsScheduleManager::proceedDatePassingEntranceTestFromPost(
                                    $request,
                                    $egeResult,
                                    $canChangeDateExamFrom1C,
                                    $transaction,
                                    $msgBox
                                );

                                $hasChanges |= $hasChangesTmp;
                            }
                        }
                    } else {
                        EntrantTestManager::errorMessageRecorder(
                            Yii::t(
                                'abiturient/bachelor/ege/all',
                                'Сообщение о валидации результатов ВИ; на стр. ВИ: `Ошибка валидации вступительных испытаний.`'
                            ),
                            $egeResult->errors,
                            'proceedEntrantTestFromPost',
                            $msgBox
                        );

                        $hasError = true;
                        $transaction->rollBack();
                        break;
                    }
                }
            }
        }
        if (!$hasError) {
            $transaction->commit();
            EntrantTestManager::successMessageRecorder(
                Yii::t(
                    'abiturient/bachelor/ege/all',
                    'Сообщение об успешном сохранении результатов ВИ; на стр. ВИ: `Информация о вступительных испытаниях успешно сохранена.`'
                ),
                $msgBox
            );

            return [
                'hasError' => false,
                'hasChanges' => $hasChanges,
            ];
        }

        return [
            'hasError' => true,
            'hasChanges' => $hasChanges,
        ];
    }

    





    public static function proceedEntrantTestFrom1C(BachelorApplication $application, array $rawData): bool
    {
        EntrantTestManager::$memorizeReferences = [];
        $hasCorrectCitizenship = BachelorResultCentralizedTesting::hasCorrectCitizenship($application);

        foreach ($rawData as $rawDisciplineFrom1C) {
            

            
            $disciplineFrom1C = ToAssocCaster::getAssoc($rawDisciplineFrom1C);

            $cgetChildDisciplineUid = '';
            $cgetDisciplineUid = EntrantTestManager::extractUidFromRefType(
                StoredDisciplineReferenceType::class,
                'SubjectRef',
                $disciplineFrom1C
            );

            $isParentSubjectRefEmpty = EmptyCheck::isEmpty(
                ArrayHelper::getValue(
                    $disciplineFrom1C,
                    'ParentSubjectRef'
                )
            );
            $isParentSubjectReferenceTypeEmpty = ReferenceTypeManager::isReferenceTypeEmpty(
                ArrayHelper::getValue(
                    $disciplineFrom1C,
                    'ParentSubjectRef'
                )
            );
            if (!$isParentSubjectRefEmpty && !$isParentSubjectReferenceTypeEmpty) {
                $cgetChildDisciplineUid = $cgetDisciplineUid;
                $cgetDisciplineUid = EntrantTestManager::extractUidFromRefType(
                    StoredDisciplineReferenceType::class,
                    'ParentSubjectRef',
                    $disciplineFrom1C
                );
            }

            $paramName = EntrantTestManager::selectBetweenTwoPossibleNames(
                'EntranceTestTypeRef',
                'EntranceTestResultSourceRef',
                $disciplineFrom1C
            );
            $cgetExamFormUid = EntrantTestManager::extractUidFromRefType(
                StoredDisciplineFormReferenceType::class,
                $paramName,
                $disciplineFrom1C
            );

            $egeAttributes = EntrantTestManager::fillEgeAttributesForSearch(
                $cgetExamFormUid,
                $cgetDisciplineUid,
                $cgetChildDisciplineUid
            );

            $storedChildDisciplineReferenceTypeTableName = StoredChildDisciplineReferenceType::tableAliasForJoin();
            $ege = $application->getEgeResults()
                ->joinWith('language')
                ->joinWith('cgetExamForm')
                ->joinWith('cgetDiscipline')
                ->joinWith('specialRequirement')
                ->joinWith("cgetChildDiscipline $storedChildDisciplineReferenceTypeTableName")
                ->andWhere($egeAttributes)
                ->one();
            if (empty($ege)) {
                $ege = new EgeResult();
            }
            $ege->setAttributes(
                EntrantTestManager::convertAttrToCreateAction(
                    EntrantTestManager::fillEgeAttributes(
                        $application,
                        $cgetExamFormUid,
                        $cgetDisciplineUid,
                        $cgetChildDisciplineUid,
                        $disciplineFrom1C
                    )
                )
            );

            if (isset($disciplineFrom1C['AdditionalElement'])) {
                $additionalElements = BaseApplicationPackageBuilder::convertAdditionalElement($disciplineFrom1C['AdditionalElement']);
                if (isset($additionalElements['Reason'])) {
                    $reasonForExam = DictionaryReasonForExam::find()->where(['code' => $additionalElements['Reason']])->one();
                    if ($reasonForExam) {
                        $ege->reason_for_exam_id = $reasonForExam->id;
                    }
                }
            }

            if ($ege->validate()) {
                if (!$ege->save(false)) {
                    Yii::error('Ошибка сохранения ВИ', 'ExamsScheduleManager.proceedEntrantTestFrom1C');
                    return false;
                }

                if ($ege->isExam()) {
                    if ($hasCorrectCitizenship) {
                        if (ArrayHelper::isAssociative($rawDisciplineFrom1C)) {
                            $recalculation = isset($rawDisciplineFrom1C['Recalculation']) ? $rawDisciplineFrom1C['Recalculation'] : [];
                        } else {
                            $recalculation = $rawDisciplineFrom1C->Recalculation ?? [];
                        }
                        if ($recalculation) {
                            if (!CentralizedTestingManager::proceedCentralizedTestingFrom1C($ege, $recalculation)) {
                                return false;
                            }
                        } else {
                            if (!CentralizedTestingManager::archiveIfExist($ege->id)) {
                                Yii::error('Ошибка архивирования ЦТ, когда из Информационной системы вуза пришла пустая структура ЦТ', 'ExamsScheduleManager.proceedEntrantTestFrom1C');
                                return false;
                            }
                        }
                    }

                    if (ArrayHelper::isAssociative($rawDisciplineFrom1C)) {
                        $examsSchedule = isset($rawDisciplineFrom1C['ExamsSchedule']) ? $rawDisciplineFrom1C['ExamsSchedule'] : [];
                    } else {
                        $examsSchedule = isset($rawDisciplineFrom1C->ExamsSchedule) ? $rawDisciplineFrom1C->ExamsSchedule : [];
                    }
                    if (isset($examsSchedule)) {
                        if (!is_array($examsSchedule) || ArrayHelper::isAssociative($examsSchedule)) {
                            $examsSchedule = [$examsSchedule];
                        }
                        if (!ExamsScheduleManager::updateStructureTo1C($ege, $examsSchedule)) {
                            Yii::error('Ошибка обновления расписания вступительных испытаний', 'ExamsScheduleManager.proceedEntrantTestFrom1C');
                            return false;
                        }
                    }
                }
            } else {
                $errors = print_r($ege->errors, true);
                Yii::error("Ошибка валидации ВИ: {$errors}", 'ExamsScheduleManager.proceedEntrantTestFrom1C');
                return false;
            }
        }

        return true;
    }

    




    private static function convertAttrToCreateAction($egeAttributes): array
    {
        $newAttrs = [];
        $REFERENCE_ID_FIELDS = EntrantTestManager::REFERENCE_ID_FIELDS();
        $REFERENCE_UID_FIELDS = EntrantTestManager::REFERENCE_UID_FIELDS();
        foreach ($egeAttributes as $attr => $value) {
            $newAttrs[$attr] = $value;

            $attributeIndexList = array_keys(
                array_filter(
                    $REFERENCE_UID_FIELDS,
                    function ($attribute) use ($attr) {
                        return $attribute == $attr;
                    }
                )
            );
            if (!empty($attributeIndexList)) {
                foreach ($attributeIndexList as $index) {
                    $newAttr = $REFERENCE_ID_FIELDS[$index];
                    $newValue = ArrayHelper::getValue(EntrantTestManager::$memorizeReferences, "{$index}.{$value}.id");

                    if (!empty($newAttr) && !empty($newValue)) {
                        $newAttrs[$newAttr] = $newValue;
                    }
                }
            }
        }

        return $newAttrs;
    }

    








    private static function selectBetweenTwoPossibleNames(
        string $name1,
        string $name2,
        array  $rawDataFrom1C
    ): string
    {
        $paramName = $name1;
        if (!key_exists($name1, $rawDataFrom1C)) {
            $paramName = $name2;
        }

        return $paramName;
    }

    






    private static function fillEgeAttributesForSearch(
        string $cgetExamFormUid,
        string $cgetDisciplineUid,
        string $cgetChildDisciplineUid
    ): array
    {
        $egeAttributes = [];

        if (!EmptyCheck::isEmpty($cgetExamFormUid)) {
            $storedDisciplineFormReferenceTypeTableName = StoredDisciplineFormReferenceType::tableName();
            $egeAttributes["{$storedDisciplineFormReferenceTypeTableName}.reference_uid"] = $cgetExamFormUid;
        }

        if (!EmptyCheck::isEmpty($cgetDisciplineUid)) {
            $storedDisciplineReferenceTypeTableName = StoredDisciplineReferenceType::tableName();
            $egeAttributes["{$storedDisciplineReferenceTypeTableName}.reference_uid"] = $cgetDisciplineUid;
        }

        if (!EmptyCheck::isEmpty($cgetChildDisciplineUid)) {
            $storedChildDisciplineReferenceTypeTableName = StoredChildDisciplineReferenceType::tableAlias();
            $egeAttributes["{$storedChildDisciplineReferenceTypeTableName}.reference_uid"] = $cgetChildDisciplineUid;
        }

        return $egeAttributes;
    }

    








    private static function fillEgeAttributes(
        BachelorApplication $application,
        string              $cgetExamFormUid,
        string              $cgetDisciplineUid,
        string              $cgetChildDisciplineUid,
        array               $rawDataFrom1C
    ): array
    {
        $egeAttributes = EntrantTestManager::fillEgeAttributesForSearch(
            $cgetExamFormUid,
            $cgetDisciplineUid,
            $cgetChildDisciplineUid
        );

        $egeAttributes['application_id'] = $application->id;
        $egeAttributes['readonly'] = (bool)ArrayHelper::getValue($rawDataFrom1C, 'ReadOnly', false);
        $egeAttributes['status'] = ((bool)ArrayHelper::getValue($rawDataFrom1C, 'Approved', false) ? EgeResult::STATUS_VERIFIED : EgeResult::STATUS_STAGED);

        $bufferValue = EntrantTestManager::extractUidFromRefType(
            ForeignLanguage::class,
            'LanguageRef',
            $rawDataFrom1C
        );
        if (!EmptyCheck::isEmpty($bufferValue)) {
            $foreignLanguageTableName = ForeignLanguage::tableName();
            $egeAttributes["{$foreignLanguageTableName}.ref_key"] = $bufferValue;
        }

        $paramName = EntrantTestManager::selectBetweenTwoPossibleNames(
            'EgeYear',
            'Year',
            $rawDataFrom1C
        );
        $egeAttributes['egeyear'] = (string)ArrayHelper::getValue($rawDataFrom1C, $paramName);

        $paramName = EntrantTestManager::selectBetweenTwoPossibleNames(
            'Mark',
            'Ball',
            $rawDataFrom1C
        );
        $egeAttributes['discipline_points'] = (string)ArrayHelper::getValue($rawDataFrom1C, $paramName);

        $paramName = EntrantTestManager::selectBetweenTwoPossibleNames(
            'SpecialRequirementsRefs',
            'SpecialRequirementRef',
            $rawDataFrom1C
        );
        $bufferValue = EntrantTestManager::extractUidFromRefType(
            SpecialRequirementReferenceType::class,
            $paramName,
            $rawDataFrom1C
        );
        if (!EmptyCheck::isEmpty($bufferValue)) {
            $specialRequirementReferenceTypeTableName = SpecialRequirementReferenceType::tableName();
            $egeAttributes["{$specialRequirementReferenceTypeTableName}.reference_uid"] = $bufferValue;
        }

        return $egeAttributes;
    }

    






    public static function archiveNotActualEge(
        BachelorApplication $application,
        array               $egeNotToArchive,
        bool                $ignoreReadonlyFlag = false
    ): void
    {
        $tnEgeResult = EgeResult::tableName();

        $egeToArchive = $application->getEgeResults()
            ->andWhere(['NOT IN', "{$tnEgeResult}.entrance_test_junction", $egeNotToArchive]);
        if (!$ignoreReadonlyFlag) {
            $egeToArchive = $egeToArchive->andWhere(['=', "{$tnEgeResult}.readonly", false]);
        }

        EntrantTestManager::archiveNotActualData($egeToArchive->all());
    }

    





    public static function archiveNotActualEgeExceptReadOnly(BachelorApplication $application, array $egeNotToArchive): void
    {
        EntrantTestManager::archiveNotActualEge($application, $egeNotToArchive);
    }

    





    public static function archiveNotActualEgeWithReadOnly(BachelorApplication $application, array $egeNotToArchive): void
    {
        EntrantTestManager::archiveNotActualEge($application, $egeNotToArchive, true);
    }

    









    public static function archiveNotActualEntranceTestSet(
        BachelorApplication $application,
                            $testSetIdList,
        string              $operator = 'NOT IN',
        bool                $ignoreReadonlyFlag = false
    ): void
    {
        $tnEgeResult = EgeResult::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        $testSetToArchive = BachelorEntranceTestSet::find()
            ->joinWith('egeResultByEntranceTestParamsOnly')
            ->joinWith('bachelorSpeciality')
            ->andWhere([$operator, "{$tnBachelorEntranceTestSet}.id", $testSetIdList])
            ->andWhere([
                "{$tnBachelorEntranceTestSet}.archive" => false,
                "{$tnEgeResult}.application_id" => $application->id,
                "{$tnBachelorSpeciality}.application_id" => $application->id,
            ]);
        
        
        
        
        
        
        

        EntrantTestManager::archiveNotActualData($testSetToArchive->all());
    }

    







    public static function archiveNotActualEntranceTestSetExceptReadOnly(
        BachelorApplication $application,
                            $testSetIdList,
        string              $operator = 'NOT IN'
    ): void
    {
        EntrantTestManager::archiveNotActualEntranceTestSet($application, $testSetIdList, $operator);
    }

    





    public static function archiveNotActualEntranceTestSetWithReadOnly(BachelorApplication $application, $testSetIdList): void
    {
        EntrantTestManager::archiveNotActualEntranceTestSet($application, $testSetIdList, 'NOT IN', true);
    }

    





    public static function createNewRecord(string $class, array $attributes)
    {
        $new = new $class();
        foreach ($attributes as $attr => $val) {
            $new->{$attr} = $val;
        }
        if ($new->validate()) {
            if (!$new->save(false)) {
                Yii::error(
                    "Ошибка сохранения `{$class}`." . PHP_EOL . print_r([$attributes], true),
                    'EntrantTestManager.createNewRecord'
                );
                return null;
            }
        } else {
            Yii::error(
                "Ошибка валидации `{$class}`." . PHP_EOL . print_r([
                    'attributes' => $attributes,
                    'errors' => $new->errors,
                ], true),
                'EntrantTestManager.createNewRecord'
            );
            return null;
        }

        return $new;
    }

    











    public static function getOrCreateEntrantTestSet(
        BachelorApplication $application,
        BachelorSpeciality  $specialities,
        int                 $disciplineId,
        int                 $childDisciplineId,
        int                 $examFormId,
        int                 $priority
    ): array
    {
        if (empty($childDisciplineId)) {
            $childDisciplineId = null;
        }
        $created = false;
        $tnEgeResult = EgeResult::tableName();
        $set = $specialities->getBachelorEntranceTestSets()
            ->joinWith('egeResultByEntranceTestParamsOnly')
            ->andWhere([
                "{$tnEgeResult}.application_id" => $application->id,
                "{$tnEgeResult}.cget_exam_form_id" => $examFormId,
                "{$tnEgeResult}.cget_discipline_id" => $disciplineId,
                "{$tnEgeResult}.cget_child_discipline_id" => $childDisciplineId,
            ])
            ->one();

        if (EmptyCheck::isEmpty($set)) {
            $ege = EntrantTestManager::getOrCreateEgeResult(
                $application,
                $disciplineId,
                (int)$childDisciplineId,
                $examFormId
            );

            $set = EntrantTestManager::createNewRecord(
                BachelorEntranceTestSet::class,
                [
                    'priority' => $priority,
                    'bachelor_speciality_id' => $specialities->id,
                    'entrance_test_junction' => $ege->entrance_test_junction,
                ]
            );
            $created = true;
            if (!$set) {
                throw new UserException('Ошибка создания набора вступительных испытаний.');
            }
        }

        return [$set, $created];
    }

    









    private static function getOrCreateEgeResult(
        BachelorApplication $application,
        int                 $disciplineId,
        int                 $childDisciplineId,
        int                 $examFormId
    ): EgeResult
    {
        if (empty($childDisciplineId)) {
            $childDisciplineId = null;
        }

        $tnEgeResult = EgeResult::tableName();
        $ege = $application->getEgeResults()
            ->andWhere([
                "{$tnEgeResult}.cget_exam_form_id" => $examFormId,
                "{$tnEgeResult}.cget_discipline_id" => $disciplineId,
                "{$tnEgeResult}.cget_child_discipline_id" => $childDisciplineId,
            ])
            ->one();

        if (EmptyCheck::isEmpty($ege)) {
            $ege = EntrantTestManager::createNewRecord(
                EgeResult::class,
                [
                    'application_id' => $application->id,
                    'cget_exam_form_id' => $examFormId,
                    'cget_discipline_id' => $disciplineId,
                    'cget_child_discipline_id' => $childDisciplineId,
                ]
            );
            if (!$ege) {
                throw new UserException('Ошибка создания вступительного испытания.');
            }
        }

        return $ege;
    }
}
