<?php

namespace common\components\EntrantTestManager;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\CodeSettingsManager\exceptions\EntityNotFoundByCodeException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\EgeResult;
use stdClass;
use Throwable;
use Yii;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class CentralizedTestingManager extends BaseEntrantTestManager
{
    







    public static function proceedCentralizedTestingFromPost(
        Request     $request,
        EgeResult   $egeResult,
        Transaction $transaction,
        array      &$msgBox = null
    ): array {
        $hasChanges = false;

        
        $centralizedTesting = $egeResult->getOrBuildCentralizedTesting();

        $postData = CentralizedTestingManager::postDataExtractor(
            $request->post(),
            "{$centralizedTesting->formName()}.{$egeResult->id}"
        );

        if ($centralizedTesting->load($postData, '')) {
            $centralizedTesting->mark = ArrayHelper::getValue($postData, 'mark', 0);
            if ($centralizedTesting->validate()) {
                if (($hasChanges = $centralizedTesting->hasChangedAttributes()) && !$centralizedTesting->save(false)) {
                    if (!$centralizedTesting->checkIfDocumentIsChanged([], false)) {
                        $centralizedTesting->setDocumentCheckStatusNotVerified();
                        $centralizedTesting->save(['document_check_status_ref_id']);
                    }

                    CentralizedTestingManager::errorMessageRecorder(
                        Yii::t(
                            'abiturient/bachelor/centralized_testing/all',
                            'Сообщение о не успешном сохранении результатов ЦТ; на стр. ВИ: `Ошибка сохранения результатов централизованного тестирования/экзамена.`'
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
                CentralizedTestingManager::errorMessageRecorder(
                    Yii::t(
                        'abiturient/bachelor/centralized_testing/all',
                        'Сообщение о валидации результатов ЦТ; на стр. ВИ: `Ошибка валидации результатов централизованного тестирования/экзамена.`'
                    ),
                    $egeResult->errors,
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

        return [
            'hasError' =>  false,
            'hasChanges' => $hasChanges,
        ];
    }

    





    public static function buildRecalculationFor1C(EgeResult $ege, bool $buildForFullPackage = false): array
    {
        $centralizedTesting = $ege->getOrBuildCentralizedTesting();

        $returnStructure = [
            'Document' => CentralizedTestingManager::buildEmptyDocument($buildForFullPackage),
            'Year' => null,
            'PassedSubjectRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
            'Mark' => null,
            'Reason' => null,
        ];

        ArrayHelper::setValue($returnStructure, 'Mark', $centralizedTesting->mark);
        ArrayHelper::setValue($returnStructure, 'Year', $centralizedTesting->yearFor1c);
        if ($buildForFullPackage) {
            ArrayHelper::setValue($returnStructure, 'Document.DocNumber', $centralizedTesting->number);
            ArrayHelper::setValue($returnStructure, 'Document.DocSeries', $centralizedTesting->series);
            ArrayHelper::setValue($returnStructure, 'Document.ReadOnly', $centralizedTesting->read_only ? 1 : 0);
            ArrayHelper::setValue(
                $returnStructure,
                'Document.DocumentCheckStatusRef',
                $centralizedTesting->buildDocumentCheckStatusRefType()
            );
        } else {
            ArrayHelper::setValue($returnStructure, 'Document.DocumentNumber', $centralizedTesting->number);
            ArrayHelper::setValue($returnStructure, 'Document.DocumentSeries', $centralizedTesting->series);
        }
        ArrayHelper::setValue($returnStructure, 'Reason', BachelorResultCentralizedTesting::CT_BELARUS);
        ArrayHelper::setValue(
            $returnStructure,
            'PassedSubjectRef',
            ReferenceTypeManager::GetReference($centralizedTesting->passedSubjectRef)
        );

        return $returnStructure;
    }

    




    private static function buildEmptyDocument(bool $buildForFullPackage = false): array
    {
        try {
            $docTypeRef = ReferenceTypeManager::GetReference(
                CodeSettingsManager::GetEntityByCode('centralized_testing_doc_type_guid')
            );
        } catch (EntityNotFoundByCodeException $e) {
            $docTypeRef = ReferenceTypeManager::getEmptyRefTypeArray(DocumentType::class);
        } catch (Throwable $e) {
            Yii::error("Ошибка получения типа документа для ЦТ, по причине: `{$e->getMessage()}`", 'buildEmptyDocument');

            throw $e;
        }

        if ($buildForFullPackage) {
            return [
                'DocumentTypeRef' => $docTypeRef,
                'DocSeries' => null,
                'DocNumber' => null,
                'IssueDate' => CentralizedTestingManager::EMPTY_DATE,
                'DocumentCheckStatusRef' => ReferenceTypeManager::GetReference(
                    (new BachelorResultCentralizedTesting),
                    'notVerifiedStatusDocumentChecker'
                ),
                'ReadOnly' => 0,
            ];
        }

        return [
            'DocumentType' => null,
            'DocumentTypeRef' => $docTypeRef,
            'DocumentSeries' => null,
            'DocumentNumber' => null,
            'DocumentDate' => CentralizedTestingManager::EMPTY_DATE,
            'DocumentOrganization' => null,
        ];
    }

    





    public static function proceedCentralizedTestingFrom1C(EgeResult $egeResult, $rawData): bool
    {
        if (!$rawData && !CentralizedTestingManager::archiveIfExist($egeResult->id)) {
            Yii::error('Ошибка архивирования ЦТ', 'proceedCentralizedTestingFrom1C');
            return false;
        }

        $mark = ArrayHelper::getValue($rawData, 'Mark');
        $year = date('Y', strtotime(ArrayHelper::getValue($rawData, 'Year')));
        $documentNumber = ArrayHelper::getValue(
            $rawData,
            'Document.DocumentNumber',
            ArrayHelper::getValue($rawData, 'Document.DocNumber')
        );
        $documentSeries = ArrayHelper::getValue(
            $rawData,
            'Document.DocumentSeries',
            ArrayHelper::getValue($rawData, 'Document.DocSeries')
        );
        $passedSubjectRefId = ArrayHelper::getValue(
            ReferenceTypeManager::GetOrCreateReference(
                StoredDisciplineReferenceType::class,
                ArrayHelper::getValue($rawData, 'PassedSubjectRef')
            ),
            'id'
        );
        $documentCheckStatusRef = ToAssocCaster::getAssoc(ArrayHelper::getValue($rawData, 'Document.DocumentCheckStatusRef', []));
        $documentReadOnly = (bool) ArrayHelper::getValue($rawData, 'Document.ReadOnly', false);

        $tnBachelorResultCentralizedTesting = BachelorResultCentralizedTesting::tableName();
        $centralizedTesting = BachelorResultCentralizedTesting::find()
            ->andWhere([
                "{$tnBachelorResultCentralizedTesting}.archive" => false,
                "{$tnBachelorResultCentralizedTesting}.egeresult_id" => $egeResult->id,

                "{$tnBachelorResultCentralizedTesting}.mark" => $mark,
                "{$tnBachelorResultCentralizedTesting}.year" => $year,
                "{$tnBachelorResultCentralizedTesting}.number" => $documentNumber,
                "{$tnBachelorResultCentralizedTesting}.series" => $documentSeries,
                "{$tnBachelorResultCentralizedTesting}.passed_subject_ref_id" => $passedSubjectRefId,
            ])
            ->one();

        if (!$centralizedTesting) {
            if (!CentralizedTestingManager::archiveIfExist($egeResult->id)) {
                Yii::error('Ошибка архивирования ЦТ', 'proceedCentralizedTestingFrom1C');
                return false;
            }

            $centralizedTesting = new BachelorResultCentralizedTesting();
            $centralizedTesting->mark = $mark;
            $centralizedTesting->year = $year;
            $centralizedTesting->number = $documentNumber;
            $centralizedTesting->series = $documentSeries;
            $centralizedTesting->egeresult_id = $egeResult->id;
            $centralizedTesting->passed_subject_ref_id = $passedSubjectRefId;
        }
        $centralizedTesting->read_only = $documentReadOnly;
        $centralizedTesting->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        if ($centralizedTesting->validate()) {
            if (!$centralizedTesting->save(false)) {
                Yii::error('Ошибка сохранения ЦТ', 'proceedCentralizedTestingFrom1C');
                return false;
            }
        } else {
            $errors = print_r($centralizedTesting->errors, true);
            Yii::error("Ошибка валидации не актуальных ЦТ: {$errors}", 'proceedCentralizedTestingFrom1C');
            return false;
        }

        return true;
    }

    







    public static function archiveIfExist(int $egeResultId): bool
    {
        $centralizedTesting = BachelorResultCentralizedTesting::findOne([
            'archive' => false,
            'egeresult_id' => $egeResultId,
        ]);

        if (!$centralizedTesting) {
            return true;
        }

        return $centralizedTesting->archive();
    }
}
