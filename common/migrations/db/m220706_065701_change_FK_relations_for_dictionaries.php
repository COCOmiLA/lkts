<?php

use common\components\Migration\MigrationWithChangeFkRelations;




class m220706_065701_change_FK_relations_for_dictionaries extends MigrationWithChangeFkRelations
{
    


    public function safeUp()
    {
        $allowNamePatternsForReftable = [
            '/cget/',
            '/filter/',
            '/dictionary/',
            '/document_type/',
            '/reference_type/',
        ];
        return $this->changeAllForeignKeys('SET NULL', $allowNamePatternsForReftable);
    }

    


    public function safeDown()
    {
        return $this->changeAllForeignKeys('CASCADE');
    }
}
