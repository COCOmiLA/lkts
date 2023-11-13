<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use yii\db\Migration;




class m211001_104106_add_code_settings extends Migration
{
    


    public function safeUp()
    {
        BachelorApplication::updateAll(['status' => ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL], ['status' => ApplicationInterface::STATUS_WANTS_TO_BE_REMOTE]);
    }

    


    public function safeDown()
    {
    }
}
