<?php
namespace api\modules\moderator\modules\v1\DTO\ReferenceType;


use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotFindReferenceException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use yii\web\NotFoundHttpException;





class UserRefDTO extends BaseReferenceTypeDTO
{
    public function getStoredReferenceTypeClass(): string
    {
        return StoredUserReferenceType::class;
    }

    






    public function getStoredReferenceType(): ?StoredReferenceType
    {
        if(ReferenceTypeManager::isReferenceTypeEmpty((array)$this->serializedData)) {
            return null;
        }
        $ref = ReferenceTypeManager::GetOrCreateReference($this->getStoredReferenceTypeClass(), $this->serializedData);
        if($ref === null) {
            throw new NotFoundHttpException('Не найден ReferenceType. ' . self::getStoredReferenceTypeClass());
        }
        return $ref;
    }
}