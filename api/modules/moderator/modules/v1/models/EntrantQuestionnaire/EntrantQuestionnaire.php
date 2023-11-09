<?php

namespace api\modules\moderator\modules\v1\models\EntrantQuestionnaire;


use common\modules\abiturient\models\AbiturientQuestionary;

class EntrantQuestionnaire extends AbiturientQuestionary
{
    public function getPassportData()
    {
        return $this->getRawPassportData()->andOnCondition([EntrantPassport::tableName() . '.archive' => false]);
    }
    
    public function getRawPassportData()
    {
        return $this->hasMany(EntrantPassport::class, ['questionary_id' => 'id']);
    }
}
