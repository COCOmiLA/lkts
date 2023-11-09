<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\User;
use common\models\UserRegistrationEmailConfirm;




class m201120_133946_resolve_old_members_email_confirmation extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $users = User::find()->all();
        foreach ($users as $user) {
            $emailConfirm = new UserRegistrationEmailConfirm();
            $emailConfirm->user_id = $user->id;
            $emailConfirm->status = UserRegistrationEmailConfirm::STATUS_ACTIVE;
            $emailConfirm->save();
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
