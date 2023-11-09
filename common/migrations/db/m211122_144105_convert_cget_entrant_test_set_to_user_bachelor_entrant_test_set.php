<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorEntranceTestSet;
use yii\db\Query;




class m211122_144105_convert_cget_entrant_test_set_to_user_bachelor_entrant_test_set extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tnBachelorEgeresult = '{{%bachelor_egeresult}}';
        $tnCgetEntranceTest = '{{%cget_entrance_test}}';
        $tnBachelorSpeciality = '{{%bachelor_speciality}}';
        $tnBachelorEntranceTestSet = '{{%bachelor_entrance_test_set}}';
        $tnAdmissionCategories = '{{%dictionary_admission_categories}}';

        try {
            $categoryOlympiad = Yii::$app->configurationManager->getCode('category_olympiad');
        } catch (Throwable $th) {
            $categoryOlympiad = '';
        }

        $specialities = (new Query())
            ->select("{$tnBachelorSpeciality}.*")
            ->from($tnBachelorSpeciality)
            ->leftJoin($tnBachelorEntranceTestSet, "{$tnBachelorSpeciality}.id = {$tnBachelorEntranceTestSet}.bachelor_speciality_id")
            ->andWhere(["{$tnBachelorEntranceTestSet}.id" => null])
            ->andWhere(['not', ["{$tnBachelorSpeciality}.cget_entrance_test_set_id" => null]]);
        if (!empty($categoryOlympiad)) {
            $specialities = $specialities
                ->leftJoin($tnAdmissionCategories, "{$tnBachelorSpeciality}.admission_category_id = {$tnAdmissionCategories}.id")
                ->andWhere([
                    '!=',
                    "{$tnAdmissionCategories}.ref_key",
                    $categoryOlympiad
                ]);
        }
        $specialities = $specialities->all();

        if (empty($specialities)) {
            return true;
        }

        foreach ($specialities as $specialty) {
            $oldSets = (new Query())
                ->select("{$tnCgetEntranceTest}.*, {$tnBachelorEgeresult}.id AS ege_id")
                ->from($tnCgetEntranceTest)
                ->leftJoin(
                    $tnBachelorEgeresult,
                    "
                        {$tnCgetEntranceTest}.subject_ref_id = {$tnBachelorEgeresult}.cget_discipline_id AND
                        {$tnCgetEntranceTest}.entrance_test_result_source_ref_id = {$tnBachelorEgeresult}.cget_exam_form_id
                    "
                )
                ->andWhere([
                    "{$tnCgetEntranceTest}.archive" => false,
                    "{$tnBachelorEgeresult}.application_id" => (int)$specialty['application_id'],
                    "{$tnCgetEntranceTest}.cget_entrance_test_set_id" => (int)$specialty['cget_entrance_test_set_id'],
                ])
                ->all();

            if (empty($oldSets)) {
                continue;
            }

            foreach ($oldSets as $set) {
                $newSetExists = (new Query())
                    ->select("{$tnBachelorEntranceTestSet}.*")
                    ->from($tnBachelorEntranceTestSet)
                    ->andWhere([
                        "{$tnBachelorEntranceTestSet}.bachelor_egeresult_id" => (int)$set['ege_id'],
                        "{$tnBachelorEntranceTestSet}.bachelor_speciality_id" => (int)$specialty['id'],
                    ])
                    ->exists();

                if (!$newSetExists) {
                    $this->insert(
                        $tnBachelorEntranceTestSet,
                        [
                            'bachelor_egeresult_id' => (int)$set['ege_id'],
                            'bachelor_speciality_id' => (int)$specialty['id'],

                            'updated_at' => time(),
                            'created_at' => time(),
                        ]
                    );
                }
            }
        }
    }

    


    public function safeDown()
    {
        
        
        
        

        
        
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        Yii::$app->db
            ->createCommand()
            ->truncateTable($tnBachelorEntranceTestSet)
            ->execute();
        return true;
    }
}
