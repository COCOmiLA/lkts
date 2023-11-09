<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\UUIDManager;




class m210310_072239_index_all_user_uuid extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        foreach (\common\models\User::find()->andWhere(['system_uuid' => null])->batch(100) as $users) {
            foreach ($users as $user) {
                $user->system_uuid = UUIDManager::GetUUID();
                $user->save(false);
            }
        }
    }

    


    public function safeDown()
    {
        return true;
    }
}
