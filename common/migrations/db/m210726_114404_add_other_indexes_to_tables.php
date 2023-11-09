<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210726_114404_add_other_indexes_to_tables extends MigrationWithDefaultOptions
{

    private const ARRAY_FIELDS_TO_BE_INDEXED = [
        'abiturient_questionary' => 'user_id',
        'application_history' => 'application_id',
        'application_moderate_history' => ['application_id', 'user_id'],
        'bachelor_application' => 'user_id',
        'bachelor_speciality' => ['application_id', 'speciality_id'],
        'individual_achievement' => 'user_id',
        'parent_data' => 'questionary_id',
        'passport_data' => 'questionary_id',
        'personal_data' => 'questionary_id',
        'user_profile' => 'user_id',
    ];
    
    


    public function safeUp()
    {
        foreach (self::ARRAY_FIELDS_TO_BE_INDEXED as $table => $field) {
            if(!is_array($field)) {
                $this->createIndex(
                    $this->getIndexName($table, $field),
                    "{{%$table}}",
                    $field
                );
            } else {
                foreach ($field as $item) {
                    $this->createIndex(
                        $this->getIndexName($table, $item),
                        "{{%$table}}",
                        $item
                    );
                }
            }
        }
    }

    


    public function safeDown()
    {
        foreach (self::ARRAY_FIELDS_TO_BE_INDEXED as $table => $field) {
        }
        foreach (self::ARRAY_FIELDS_TO_BE_INDEXED as $table => $field) {
            if(!is_array($field)) {
                $this->dropIndex($this->getIndexName($table, $field), "{{%$table}}");
            } else {
                foreach ($field as $item) {
                    $this->dropIndex($this->getIndexName($table, $item), "{{%$table}}");
                }
            }
        }
    }

    




    private function getIndexName($table, $field): string {
        return "{{%idx-$table-$field}}";
    }
}
