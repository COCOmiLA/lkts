<?php


namespace common\modules\abiturient\models\repositories;


use common\models\Regulation;

class RegulationRepository
{
    




    public static function GetNotExistingRegulationsForEntity($related_entity, $attachedRegulations): array
    {
        return Regulation::find()
            ->andWhere(['related_entity' => $related_entity])
            ->andWhere(['not', ['id' => $attachedRegulations]])
            ->all();
    }
}