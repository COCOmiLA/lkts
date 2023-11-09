<?php

use backend\models\MainPageInstructionFile;
use backend\models\MainPageInstructionHeader;
use backend\models\MainPageInstructionText;
use backend\models\MainPageSetting;
use common\modules\abiturient\models\bachelor\ApplicationType;
use yii\web\View;





$instructions = MainPageSetting::getInstructions();

$headerCount = 0;

$maxCount = ApplicationType::getMaxSpecialityCount();

?>

<?php foreach ($instructions as $instruction) : ?>
    <?php  ?>

    <div class="row mb-3">
        <div class="col-12">
            <?php $dataForRender = [];
            if ($instruction instanceof MainPageInstructionText) {
                $dataForRender = compact([
                    'maxCount',
                    'instruction',
                ]);
            } else if ($instruction instanceof MainPageInstructionHeader) {
                $dataForRender = [
                    'maxCount' => $maxCount,
                    'instruction' => $instruction,
                    'headerCount' => ++$headerCount,
                ];
            } else if ($instruction instanceof MainPageInstructionFile) {
                $dataForRender = compact(['instruction']);
            }

            echo $this->render(
                $instruction->getViewFileName(),
                $dataForRender,
            ); ?>
        </div>
    </div>
<?php endforeach;