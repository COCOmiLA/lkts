<?php

use common\components\exceptions\ArchiveAdmissionCampaignHandlerException;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\AvailableDocumentTypesForConcession;
use common\models\dictionary\DocumentType;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;




class m210727_082259_recover_old_preferences extends MigrationWithDefaultOptions
{
    








    private static function loadBenefitsLists(BachelorApplication $application): array
    {
        $ids = [];

        $tnPrivilege = Privilege::tableName();
        $application->archiveAdmissionCampaignHandler->handle();
        
        $preferenceArray = Privilege::find()
            ->notMarkedToDelete()
            ->active()
            ->andWhere(["{$tnPrivilege}.is_folder" => false])
            ->joinWith('admissionProcedures', false)
            ->joinWith('admissionProcedures.admissionCampaignRef', false)
            ->andWhere(['{{%admission_campaign_reference_type}}.reference_uid' => $application->type->rawCampaign->referenceType->reference_uid])
            ->andWhere(['dictionary_privileges.archive' => false])
            ->andWhere(['{{%dictionary_admission_procedure}}.archive' => false])
            ->orFilterWhere(
                ["{$tnPrivilege}.id" => $application->getPreferences()
                    ->innerJoinWith(['privilege privilege' => function ($q) {
                        $q->innerJoinWith('admissionProcedures', false); 
                    }])->select('privilege.id')]
            )
            ->orderBy("{$tnPrivilege}.description")
            ->all();
        foreach ($preferenceArray as $value) {
            $hash_key = $value->getHashCode();
            $ids[$hash_key] = $value->id;
        }

        $tnSpecialMark = SpecialMark::tableName();
        
        $specificMarkArray = SpecialMark::find()
            ->notMarkedToDelete()
            ->active()
            ->andWhere(["{$tnSpecialMark}.is_folder" => false])
            ->joinWith('admissionProcedures', false)
            ->joinWith('admissionProcedures.admissionCampaignRef', false)
            ->andWhere(['{{%admission_campaign_reference_type}}.reference_uid' => $application->type->rawCampaign->referenceType->reference_uid])
            ->andWhere(['dictionary_special_marks.archive' => false])
            ->andWhere(['{{%dictionary_admission_procedure}}.archive' => false])
            ->orFilterWhere(
                ["{$tnSpecialMark}.id" => $application->getPreferences()
                    ->innerJoinWith(['specialMark specialMark' => function ($q) {
                        $q->innerJoinWith('admissionProcedures', false); 
                    }])->select('specialMark.id')]
            )
            ->orderBy("{$tnSpecialMark}.description")
            ->all();
        foreach ($specificMarkArray as $value) {
            $hash_key = $value->getHashCode();
            $ids[$hash_key] = $value->id;
        }

        return $ids;
    }

    


    public function safeUp()
    {
        
        $applications = BachelorApplication::find()
            ->joinWith('bachelorPreferencesSpecialRight', false, 'INNER JOIN') 
            ->all();
        $count = count($applications);
        for ($i = 0; $i < $count; $i++) {
            $application = $applications[$i];
            $ids = [];

            try {
                $ids = self::loadBenefitsLists($application);
            } catch (ArchiveAdmissionCampaignHandlerException $e) {
                continue;
            }

            $preferences = $application->bachelorPreferencesSpecialRight;
            $countPref = count($preferences);

            for ($j = 0; $j < $countPref; $j++) {
                $preference = $preferences[$j];
                $preferenceCode = $preference->code;
                if (in_array($preferenceCode, array_keys($ids))) {

                    if (!is_null($preference->privilege_id)) {
                        if ($ids[$preferenceCode] !== $preference->privilege_id) {
                            $privilegeOne = Privilege::findOne($preference->privilege_id);
                            $privilegeSecond = Privilege::findOne($ids[$preferenceCode]);
                            if (!is_null($privilegeOne) && !is_null($privilegeSecond)) {
                                if (($privilegeOne->ref_key !== $privilegeSecond->ref_key) && ($privilegeOne->code === $privilegeSecond->code)) {
                                    
                                    $preference->privilege_id = $ids[$preferenceCode];
                                    if ($preference->validate(['privilege_id'])) {
                                        $preference->save(false, ['privilege_id']);
                                        $this->recoverDocumentType($application, $preference);
                                    } else {
                                        Yii::error('Невозможно сохранить информацию при восстановлении льготы: ' . print_r($preference->errors), 'VALIDATION_ERROR_WHILE_RECOVERING_PRIVILEGES');
                                    }
                                }
                            }
                        }
                    }

                    if (!is_null($preference->special_mark_id)) {
                        if ($ids[$preferenceCode] !== $preference->special_mark_id) {
                            $specialMarkOne = SpecialMark::findOne($preference->special_mark_id);
                            $specialMarkSecond = SpecialMark::findOne($ids[$preferenceCode]);
                            if (!is_null($specialMarkOne) && !is_null($specialMarkSecond)) {
                                if (($specialMarkOne->ref_key !== $specialMarkSecond->ref_key) && ($specialMarkOne->code === $specialMarkSecond->code)) {
                                    
                                    $preference->special_mark_id = $ids[$preferenceCode];
                                    if ($preference->validate(['special_mark_id'])) {
                                        $preference->save(false, ['special_mark_id']);
                                        $this->recoverDocumentType($application, $preference);
                                    } else {
                                        Yii::error('Невозможно сохранить информацию при восстановлении льготы: ' . print_r($preference->errors), 'VALIDATION_ERROR_WHILE_RECOVERING_SPECIAL_MARK');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($application);
            unset($preferences);
        }
        unset($applications);
    }

    


    public function safeDown()
    {
        return true;
    }

    




    private function recoverDocumentType(BachelorApplication $application, BachelorPreferences $preference)
    {
        $docTypes = $this->getDocTypesByBachelorPreferenceCodeAndBachelorApplication($application, $preference->code);
        if (!is_null($preference->document_type_id) && !is_null($preference->document_type)) {
            $prefDocTypeCode = $preference->document_type;
            foreach ($docTypes as $docType) {
                if ($docType['code'] === $prefDocTypeCode) {
                    $preference->document_type_id = $docType['maxid'];
                    if ($preference->validate(['document_type_id'])) {
                        $preference->save(false, ['document_type_id']);
                        unset($docType);
                        unset($docTypes);
                        return;
                    } else {
                        Yii::error('Невозможно сохранить информацию при восстановлении льготы: ' . print_r($preference->errors), 'VALIDATION_ERROR_WHILE_RECOVERING_SPECIAL_MARK');
                        return;
                    }
                }
            }
        }
        unset($docTypes);
    }

    





    private function getDocTypesByBachelorPreferenceCodeAndBachelorApplication(BachelorApplication $application, string $code = '')
    {
        $parents = explode('_', $code);
        $id_subject = $parents[0] ?: $code;
        if (count($parents) > 1 && (int)$parents[1] % 2 == 0) {
            $subject_type = 'Льготы';
        } else {
            $subject_type = 'ОсобыеОтметки';
        }
        $query = AvailableDocumentTypesForConcession::find()
            ->andWhere(['dictionary_available_document_types_for_concession.id_subject' => $id_subject])
            ->andWhere(['dictionary_available_document_types_for_concession.subject_type' => $subject_type])
            ->andWhere(['dictionary_available_document_types_for_concession.id_pk' => $application->type->campaign->code])
            ->andWhere(['dictionary_available_document_types_for_concession.archive' => false])
            ->select('dictionary_available_document_types_for_concession.document_type');
        $ValueArray = DocumentType::find()
            ->select(['maxid' => 'max(dictionary_document_type.id)', 'dictionary_document_type.code', 'dictionary_document_type.description'])
            ->andWhere(['dictionary_document_type.code' => $query])
            ->andWhere(['dictionary_document_type.archive' => false])
            ->groupBy(['dictionary_document_type.code', 'dictionary_document_type.description'])
            ->asArray()
            ->all();
        if (empty($ValueArray)) { 
            $ValueArray = DocumentType::find()
                ->select(['maxid' => 'max(dictionary_document_type.id)', 'dictionary_document_type.code', 'dictionary_document_type.description'])
                ->andWhere(['dictionary_document_type.archive' => false])
                ->groupBy(['dictionary_document_type.code', 'dictionary_document_type.description'])
                ->asArray()
                ->all();
        }
        return $ValueArray;
    }
}
