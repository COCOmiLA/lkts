<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\EntrantManager;




class m210423_143419_recover_entrant_manager_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $managers = \common\models\User::find()
            ->alias('u')
            ->leftJoin('rbac_auth_assignment', 'rbac_auth_assignment.user_id = u.id')
            ->andWhere(['rbac_auth_assignment.item_name' => \common\models\User::ROLE_MANAGER])
            ->andWhere(['status' => 1])->all();

        foreach ($managers as $manager) {
            $entrantManager = new EntrantManager();
            $entrantManager->local_manager = $manager->id;
            $entrantManager->save();
        }
    }

    


    public function safeDown()
    {
        return true;
    }

    













}
