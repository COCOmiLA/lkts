<?php

namespace common\components;

class RegulationRelationManager extends PageRelationManager
{

    






    public static function GetRelatedListWithDependentNotEmptyAbstractionForApplication(): array
    {
        return [
            
            
            static::RELATED_ENTITY_OLYMPIAD => 'getBachelorPreferencesOlymp',
            static::RELATED_ENTITY_PREFERENCE => 'getBachelorPreferencesSpecialRight',
            static::RELATED_ENTITY_TARGET_RECEPTION => 'getTargetReceptions',
        ];
    }
}
