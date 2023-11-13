<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221003_153446_reset_user_sequence extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if (Yii::$app->db->driverName === 'pgsql') {
            $max_id = intval(\common\models\User::find()->select('MAX(id)')->scalar());
            $this->execute("SELECT SETVAL('user_id_seq', $max_id);");
        }
    }

}
