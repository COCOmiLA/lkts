<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221129_085737_add_ia_group_ref_table extends MigrationWithDefaultOptions
{
    use \common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;

    


    public function safeUp()
    {
        $this->createReferenceTable('achievement_group_reference_type');
        
        $this->db->schema->refresh();

        
        $this->addColumn(
            \common\models\dictionary\IndividualAchievementType::tableName(),
            'points_in_group_are_awarded_once',
            $this->boolean()->defaultValue(false)->comment('Если true, то баллы за группу начисляются только один раз')
        );

        
        $this->addColumn(\common\models\dictionary\IndividualAchievementType::tableName(), 'achievement_group_ref_id', $this->integer()->null()->comment('Ссылка на группу индивидуальных достижений'));
        
        $this->createIndex('idx-individual_achievement-achievement_group_ref_id', \common\models\dictionary\IndividualAchievementType::tableName(), 'achievement_group_ref_id');
        
        $this->addForeignKey('fk-ia-group_ref_id', \common\models\dictionary\IndividualAchievementType::tableName(), 'achievement_group_ref_id', '{{%achievement_group_reference_type}}', 'id', 'SET NULL');
    }

    


    public function safeDown()
    {

        $this->dropForeignKey('fk-ia-group_ref_id', \common\models\dictionary\IndividualAchievementType::tableName());
        $this->dropIndex('idx-individual_achievement-achievement_group_ref_id', \common\models\dictionary\IndividualAchievementType::tableName());
        $this->dropColumn(\common\models\dictionary\IndividualAchievementType::tableName(), 'achievement_group_ref_id');
        $this->dropColumn(\common\models\dictionary\IndividualAchievementType::tableName(), 'points_in_group_are_awarded_once');
        $this->dropReferenceTable('achievement_group_reference_type');
    }
}
