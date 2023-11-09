<?php

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\StoredReferenceType\StoredAchievementCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use yii\db\Migration;




class m211007_100359_restore_curriculum_refs extends Migration
{
    


    public function up()
    {
        try {
            $this->dropForeignKey(
                '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
                '{{%dictionary_individual_achievement}}'
            );
        } catch (Throwable $e) {
            
        }
        Yii::$app->db->schema->refresh();

        foreach (IndividualAchievementType::find()->all() as $ia_type) {
            $stored_ia_curr_type = StoredAchievementCurriculumReferenceType::findOne($ia_type->ach_curriculum_ref_id);
            if ($stored_ia_curr_type) {
                $stored_curr_type = ReferenceTypeManager::GetOrCreateReference(
                    StoredCurriculumReferenceType::class,
                    $stored_ia_curr_type->buildReferenceTypeArrayTo1C()
                );
                $ia_type->ach_curriculum_ref_id = $stored_curr_type->id;
                $ia_type->save(true, ['ach_curriculum_ref_id']);
            }
        }

        $this->addForeignKey(
            '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}',
            'ach_curriculum_ref_id',
            '{{%curriculum_reference_type}}',
            'id',
            'NO ACTION'
        );

        Yii::$app->db->schema->refresh();

    }

    


    public function down()
    {
        $this->dropForeignKey(
            '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}',
            'ach_curriculum_ref_id',
            '{{%achievement_curriculum_reference_type}}',
            'id',
            'NO ACTION'
        );
        Yii::$app->db->schema->refresh();

    }

}
