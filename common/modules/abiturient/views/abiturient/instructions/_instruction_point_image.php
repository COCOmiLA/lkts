<?php

use backend\models\MainPageInstructionImage;
use yii\web\View;







$file = '';
 if ($linkedFile = $instruction->linkedFile) {
    $file = $linkedFile->upload_name;
}

?>

<img
    width="<?= $instruction->width ?>"
    height="<?= $instruction->height ?>"
    src="<?= $instruction->buildSourceUrl() ?>"
    alt="<?= $file ?>"
>