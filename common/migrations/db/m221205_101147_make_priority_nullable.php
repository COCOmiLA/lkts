<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;




class m221205_101147_make_priority_nullable extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn(BachelorSpeciality::tableName(), 'priority', $this->integer()->null());
    }

    


    public function safeDown()
    {
        $this->alterColumn(BachelorSpeciality::tableName(), 'priority', $this->integer()->notNull());
    }
}
