<?php

namespace common\components\queries;

use common\modules\abiturient\models\bachelor\BachelorSpeciality;

class EnlistedApplicationQueryForBachelorPreferences extends EnlistedApplicationQuery
{
    



    public function notInEnlistedAppWithOlympiadAndPreferences()
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorSpecialityWithOlympiad = 'bachelor_specialities_with_olympiad';

        return $this->joinWith('bachelorSpecialities')
            
            ->joinWith("rawBachelorSpecialitiesWithOlympiad {$tnBachelorSpecialityWithOlympiad}")
            ->andWhere([
                'or',
                ['IN', "{$tnBachelorSpeciality}.is_enlisted", $this->modelClass::getInQueryValue()],
                [
                    'and',
                    ['IN', "{$tnBachelorSpecialityWithOlympiad}.is_enlisted", $this->modelClass::getInQueryValue()],
                    
                    ["{$tnBachelorSpecialityWithOlympiad}.archive" => false],
                ]
            ]);
    }

    


    public function notInEnlistedAppWithOlympiad()
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();

        $joinWith = 'bachelorSpecialitiesWithOlympiad';
        return $this->joinWith($joinWith)
            ->andOnCondition(['IN', "{$tnBachelorSpeciality}.is_enlisted", $this->modelClass::getInQueryValue()]);
    }
}
