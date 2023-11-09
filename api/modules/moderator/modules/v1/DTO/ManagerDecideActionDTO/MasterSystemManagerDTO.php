<?php
namespace api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO;


use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\ReferenceType\UserRefDTO;
use common\components\EntrantModeratorManager\EntrantModeratorManager;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\MasterSystemManager;
use yii\web\NotFoundHttpException;





class MasterSystemManagerDTO extends BaseXMLSerializedDTO
{
    


    protected $ManagerRef;

    


    public function getPropertyManagerRef(): UserRefDTO
    {
        return $this->ManagerRef;
    }

    





    public function findManager(): ?MasterSystemManager {
        $refType = $this->getPropertyManagerRef()->getStoredReferenceType();
        return MasterSystemManager::findOne([
            'ref_id' => $refType->id
        ]);
    }

    







    public function getManager(): MasterSystemManager {
        
        $ref = $this->getPropertyManagerRef()->getStoredReferenceType();
        return EntrantModeratorManager::GetOrCreateMasterSystemManager($ref);
    }

    








    public function authorizeMasterSystemManager(): void {
        $manager = $this->getManager();
        \Yii::$app->user->setIdentity($manager);
    }
}