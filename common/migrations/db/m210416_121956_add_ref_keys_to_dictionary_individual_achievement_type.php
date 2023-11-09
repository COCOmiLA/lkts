<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\migrations\traits\TableOptionsTrait;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210416_121956_add_ref_keys_to_dictionary_individual_achievement_type extends MigrationWithDefaultOptions
{
    use TableOptionsTrait;
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $this->createReferenceTable('achievement_curriculum_reference_type', self::GetTableOptions());

        $this->addColumn('{{%dictionary_individual_achievement}}', 'ach_curriculum_ref_id', $this->integer()->defaultValue(null));

        $this->createIndex(
            '{{%idx-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}',
            'ach_curriculum_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}',
            'ach_curriculum_ref_id',
            '{{%achievement_curriculum_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}'
        );

        $this->dropIndex(
            '{{%idx-dictionary_individual_achievement-ach_curriculum_ref_id}}',
            '{{%dictionary_individual_achievement}}'
        );

        $this->dropColumn('{{%dictionary_individual_achievement}}', 'ach_curriculum_ref_id');

        if (Yii::$app->db->schema->getTableSchema('achievement_curriculum_reference_type') !== null) {
            $this->dropReferenceTable('achievement_curriculum_reference_type');
        }
    }
}
