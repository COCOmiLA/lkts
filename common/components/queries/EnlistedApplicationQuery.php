<?php

namespace common\components\queries;

use common\modules\abiturient\models\bachelor\BachelorSpeciality;

class EnlistedApplicationQuery extends ArchiveQuery
{
    


    public function notInEnlistedApp()
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();

        $joinWith = $this->modelClass::getJoinWith();
        return $this->joinWith($joinWith)
            ->andOnCondition(['IN', "{$tnBachelorSpeciality}.is_enlisted", $this->modelClass::getInQueryValue()]);
    }
}
