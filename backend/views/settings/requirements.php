<?php

use yii\bootstrap4\Alert;

$this->title = 'Необходимые расширения PHP';

$system_requirements_dir = Yii::getAlias('@frontend') 
        . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'src'
        . DIRECTORY_SEPARATOR . 'steps' . DIRECTORY_SEPARATOR . 'system_requirements';

$checker_dir = $system_requirements_dir . DIRECTORY_SEPARATOR . 'requirements';

if (!is_dir($checker_dir)) {
    echo Alert::widget([
        'body' => 'Не найден скрипт проверки системных требований.',
        'options' => ['class' => 'alert-danger']
    ]);
} else {
    require_once($system_requirements_dir . DIRECTORY_SEPARATOR . 'customs_requirements.php');
    $phppath = 'php';
    $requirementsChecker = getRequirementChecker($checker_dir, $phppath);
    $requirementsChecker->render();
}

?>
