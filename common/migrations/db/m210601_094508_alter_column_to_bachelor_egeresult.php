<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\EgeResult;




class m210601_094508_alter_column_to_bachelor_egeresult extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn(EgeResult::tableName(), 'discipline_id', $this->integer()->defaultValue(null));
    }

    


    public function safeDown()
    {
        $this->alterColumn(EgeResult::tableName(), 'discipline_id', $this->integer()->notNull());
    }
}
