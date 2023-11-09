<?php

namespace common\components;

use common\models\errors\RecordNotValid;
use common\models\UserRegulation;
use yii\web\Request;

class RegulationManager
{
    









    public static function handleRegulations(array $regulations, Request $request): bool
    {
        $something_changed = false;
        foreach ($regulations as $regulation) {
            $dataFromPost = $request->post($regulation->formName())[$regulation->getIndex()] ?? null;
            if ($dataFromPost) {
                $regulation->is_confirmed = $dataFromPost['is_confirmed'] ?? 0;
                if ($regulation->hasChangedAttributes()) {
                    if (!$regulation->save()) {
                        throw new RecordNotValid($regulation);
                    }
                    $something_changed = true;
                }
            }
        }
        return $something_changed;
    }
}
