<?php

namespace backend\models;

use common\models\interfaces\IMainPageInstruction;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;












class MainPageInstructionTemplate extends ActiveRecord implements IMainPageInstruction
{
    


    public function getViewFileName(): string
    {
        return '_instruction_point_template';
    }

    






    public static function getInstructionData(
        array  $postData,
        string $instructionForm,
        string $mainPageSettingId
    ): array {
        $instructionData = ArrayHelper::getValue($postData, "{$instructionForm}.{$mainPageSettingId}", []);

        return $instructionData;
    }
}
