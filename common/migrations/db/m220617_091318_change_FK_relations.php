<?php

use common\components\Migration\MigrationWithChangeFkRelations;




class m220617_091318_change_FK_relations extends MigrationWithChangeFkRelations
{
    


    public function safeUp()
    {
        return $this->changeAllForeignKeys('CASCADE');
    }

    


    public function safeDown()
    {
        return $this->changeAllForeignKeys();
    }
}
