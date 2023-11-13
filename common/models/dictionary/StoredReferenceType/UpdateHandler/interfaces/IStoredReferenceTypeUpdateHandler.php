<?php
namespace common\models\dictionary\StoredReferenceType\UpdateHandler\interfaces;

use Closure;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;






interface IStoredReferenceTypeUpdateHandler
{
    





    public function update(Closure $onBeginUpdate = null, Closure $onNextReference = null, Closure $onEndUpdate = null);

    



    public function getStoredReferenceTypeToProceed(): StoredReferenceType;

    




    public function setStoredReferenceTypeToProceed(StoredReferenceType $referenceType);
}