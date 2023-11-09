<?php


namespace common\components\EntrantModeratorManager;


use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\EntrantManager;
use common\models\MasterSystemManager;
use common\models\User;

class EntrantModeratorManager
{
    




    public static function GetOrCreateMasterSystemManager(StoredUserReferenceType $managerRef): MasterSystemManager {
        $manager = MasterSystemManager::findOne([
            'ref_id' => $managerRef->id
        ]);

        if(is_null($manager)) {
            $manager = new MasterSystemManager();
            $manager->full_name = $managerRef->reference_name;
            $manager->ref_id = $managerRef->id;
            if($manager->validate()) {
                $manager->save(false);
            } else {
                throw new EntrantManagerValidationException();
            }
        }

        return $manager;
    }

    public static function GetOrCreateEntrantModerator(IEntrantManager $manager): EntrantManager {
        $fieldToSearch = $manager instanceof User ? 'local_manager' : 'master_system_manager';

        $entity = EntrantManager::findOne([
            $fieldToSearch => $manager->id
        ]);

        if(is_null($entity)) {
            $entity = new EntrantManager();
            if($manager instanceof User) {
                $entity->local_manager = $manager->id;
            }

            if($manager instanceof MasterSystemManager) {
                $entity->master_system_manager = $manager->id;
            }

            if(!$entity->save()) {
                throw new EntrantManagerValidationException();
            }
        }

        return $entity;
    }
}